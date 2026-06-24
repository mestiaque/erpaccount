<?php

namespace ME\Erpaccount\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ME\Erpaccount\Models\Debitor;

class DebitorController extends Controller
{
    public function index(Request $request)
    {
        $query = Debitor::query()->orderBy('name');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($b) use ($search) {
                $b->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $debitors = $query->paginate(20)->withQueryString();

        if ($this->isApiRequest($request)) {
            return response()->json(['data' => $debitors]);
        }

        return view('erpaccount::phase1.debitors.index', [
            'debitors' => $debitors,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'type'     => ['nullable', 'string', 'max:50'],
            'address'  => ['nullable', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'email'    => ['nullable', 'email', 'max:100'],
            'category' => ['required', 'in:local,international'],
            'country'  => ['nullable', 'string', 'max:100'],
        ]);

        $debitor = Debitor::query()->create($validated);

        return $this->respond($request, 'Debitor created successfully.', 201, 'success', [
            'debitor_id' => $debitor->debitor_id,
        ]);
    }

    public function update(Request $request, Debitor $debitor): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'type'      => ['nullable', 'string', 'max:50'],
            'address'   => ['nullable', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'email'     => ['nullable', 'email', 'max:100'],
            'category'  => ['required', 'in:local,international'],
            'country'   => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $debitor->update($validated);

        return $this->respond($request, 'Debitor updated successfully.');
    }

    public function destroy(Request $request, Debitor $debitor): JsonResponse|RedirectResponse
    {
        $debitor->delete();

        return $this->respond($request, 'Debitor deleted successfully.');
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
