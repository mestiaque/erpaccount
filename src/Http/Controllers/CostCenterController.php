<?php

namespace ME\Erpaccount\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ME\Erpaccount\Http\Requests\CostCenterStoreRequest;
use ME\Erpaccount\Http\Requests\CostCenterUpdateRequest;
use ME\Erpaccount\Models\CostCenter;
use ME\Erpaccount\Models\CostCenterType;

class CostCenterController extends Controller
{
    public function index(Request $request)
    {
        $query = CostCenter::query()->orderBy('cost_center_type')->orderBy('cost_center_name');

        if ($request->filled('cost_center_type')) {
            $query->where('cost_center_type', $request->input('cost_center_type'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search) {
                $builder->where('cost_center_name', 'like', '%' . $search . '%')
                    ->orWhere('reference_id', 'like', '%' . $search . '%');
            });
        }

        $costCenters = $query->paginate(20)->withQueryString();
        $types = CostCenterType::query()
            ->where('is_active', true)
            ->orderBy('type_name')
            ->pluck('type_name')
            ->filter()
            ->values()
            ->all();

        $allTypes = CostCenterType::query()
            ->orderBy('type_name')
            ->get();

        if ($this->isApiRequest($request)) {
            return response()->json([
                'data' => $costCenters,
                'types' => $types,
            ]);
        }

        return view('erpaccount::phase2.cost_centers.index', [
            'costCenters' => $costCenters,
            'types' => $types,
            'allTypes' => $allTypes,
            'filters' => $request->only(['cost_center_type', 'search']),
        ]);
    }

    public function store(CostCenterStoreRequest $request): JsonResponse|RedirectResponse
    {
        $costCenter = CostCenter::query()->create($request->validated());

        return $this->respond(
            $request,
            'Cost center created successfully.',
            201,
            'success',
            ['cost_center_id' => $costCenter->cost_center_id]
        );
    }

    public function update(CostCenterUpdateRequest $request, CostCenter $costCenter): JsonResponse|RedirectResponse
    {
        $costCenter->update($request->validated());

        return $this->respond($request, 'Cost center updated successfully.');
    }

    public function destroy(Request $request, CostCenter $costCenter): JsonResponse|RedirectResponse
    {
        $costCenter->delete();

        return $this->respond($request, 'Cost center deleted successfully.');
    }

    public function storeType(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'type_name' => ['required', 'string', 'max:50', 'unique:acc_cost_center_types,type_name'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        CostCenterType::query()->create([
            'type_name' => trim((string) $validated['type_name']),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return $this->respond($request, 'Cost center type created successfully.');
    }

    public function updateType(Request $request, CostCenterType $costCenterType): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'type_name' => ['required', 'string', 'max:50', 'unique:acc_cost_center_types,type_name,' . $costCenterType->cost_center_type_id . ',cost_center_type_id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldType = $costCenterType->type_name;
        $newType = trim((string) $validated['type_name']);

        $costCenterType->update([
            'type_name' => $newType,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        if ($oldType !== $newType) {
            CostCenter::query()
                ->where('cost_center_type', $oldType)
                ->update(['cost_center_type' => $newType]);
        }

        return $this->respond($request, 'Cost center type updated successfully.');
    }

    public function destroyType(Request $request, CostCenterType $costCenterType): JsonResponse|RedirectResponse
    {
        if (CostCenter::query()->where('cost_center_type', $costCenterType->type_name)->exists()) {
            return $this->respond($request, 'This type is already used in cost centers. Update those records first.', 422, 'warning');
        }

        $costCenterType->delete();

        return $this->respond($request, 'Cost center type deleted successfully.');
    }

    private function respond(
        Request $request,
        string $message,
        int $status = 200,
        string $flashType = 'success',
        array $extra = []
    ): JsonResponse|RedirectResponse {
        if ($this->isApiRequest($request)) {
            return response()->json(array_merge(['message' => $message], $extra), $status);
        }

        return redirect()->back()->with($flashType, $message);
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->expectsJson() || str_starts_with($request->path(), 'api/');
    }
}
