-- ==========================================
-- Demo Data: Cost Centers
-- Table: acc_cost_centers
-- ==========================================

START TRANSACTION;

DELETE FROM acc_cost_centers
WHERE (cost_center_type = 'Order' AND reference_id IN (10001, 10002, 10003, 10004))
	OR (cost_center_type = 'Department' AND reference_id IN (210, 220, 230, 240))
	OR (cost_center_type = 'Machine_Line' AND reference_id IN (3101, 3102, 3103, 3104));

INSERT INTO acc_cost_centers
(cost_center_type, reference_id, cost_center_name, created_at, updated_at)
VALUES
('Order', 10001, 'Order #ORD-2026-001', NOW(), NOW()),
('Order', 10002, 'Order #ORD-2026-002', NOW(), NOW()),
('Order', 10003, 'Order #ORD-2026-003', NOW(), NOW()),
('Order', 10004, 'Order #ORD-2026-004', NOW(), NOW()),
('Department', 210, 'Production Department', NOW(), NOW()),
('Department', 220, 'Accounts & Finance', NOW(), NOW()),
('Department', 230, 'Commercial Department', NOW(), NOW()),
('Department', 240, 'Merchandising Department', NOW(), NOW()),
('Machine_Line', 3101, 'Line A - Sewing', NOW(), NOW()),
('Machine_Line', 3102, 'Line B - Cutting', NOW(), NOW()),
('Machine_Line', 3103, 'Line C - Finishing', NOW(), NOW()),
('Machine_Line', 3104, 'Line D - Packing', NOW(), NOW());

COMMIT;
