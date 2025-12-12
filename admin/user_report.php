<?php
require_once 'includes/config.php';
require_once 'includes/admin_header.php';

$page_title = "User Reports";

// Get date range filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01', strtotime('-1 month'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get all users
$sql = "
    SELECT 
        user_id,
        username,
        email,
        full_name,
        address,
        phone,
        created_at
    FROM 
        users
    WHERE 
        (created_at BETWEEN :start_date AND :end_date)
    ORDER BY 
        created_at DESC
";

$stmt = $db->prepare($sql);
$stmt->execute([
    ':start_date' => $start_date,
    ':end_date' => $end_date
]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_users = count($users);
$new_users_today = $db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn();
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">User Reports</h5>
    </div>
    <div class="card-body">
        <form method="get" class="mb-4">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="user_report.php" class="btn btn-outline-secondary ms-2">Reset</a>
                </div>
            </div>
        </form>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Users</h6>
                        <h3 class="mb-0"><?php echo $total_users; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">New Users Today</h6>
                        <h3 class="mb-0"><?php echo $new_users_today; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registered On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary view-user" 
                                    data-id="<?php echo $user['user_id']; ?>"
                                    data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                    data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                    data-fullname="<?php echo htmlspecialchars($user['full_name']); ?>"
                                    data-phone="<?php echo htmlspecialchars($user['phone']); ?>"
                                    data-address="<?php echo htmlspecialchars($user['address']); ?>"
                                    data-created="<?php echo date('M d, Y h:i A', strtotime($user['created_at'])); ?>">
                                <i class="bi bi-eye"></i> View
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details - <span id="modalUsername"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6>Basic Information</h6>
                    <p class="mb-1"><strong>User ID:</strong> <span id="modalUserId"></span></p>
                    <p class="mb-1"><strong>Full Name:</strong> <span id="modalFullName"></span></p>
                    <p class="mb-1"><strong>Email:</strong> <span id="modalEmail"></span></p>
                    <p class="mb-1"><strong>Phone:</strong> <span id="modalPhone"></span></p>
                    <p class="mb-1"><strong>Registered On:</strong> <span id="modalCreated"></span></p>
                </div>
                <div class="mb-3">
                    <h6>Address</h6>
                    <p id="modalAddress" class="text-muted"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Required JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('.datatable').DataTable({
        responsive: true,
        order: [[5, 'desc']] // Default sort by registration date descending
    });

    // Handle view user button clicks
    $('.view-user').click(function() {
        // Set modal content
        $('#modalUserId').text($(this).data('id'));
        $('#modalUsername').text($(this).data('username'));
        $('#modalFullName').text($(this).data('fullname'));
        $('#modalEmail').text($(this).data('email'));
        $('#modalPhone').text($(this).data('phone'));
        $('#modalAddress').text($(this).data('address'));
        $('#modalCreated').text($(this).data('created'));

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
        modal.show();
    });
});
</script>

<?php
require_once 'includes/admin_footer.php';
?>