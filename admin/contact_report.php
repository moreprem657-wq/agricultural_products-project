<?php
require_once 'includes/config.php';
require_once 'includes/admin_header.php';

$page_title = "Contact Messages";

// Get filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$read_status = isset($_GET['read_status']) ? $_GET['read_status'] : 'all';

// Get all contact messages
$sql = "
    SELECT 
        id,
        name,
        email,
        phone,
        subject,
        message,
        created_at,
        is_read
    FROM 
        contact_messages
    WHERE 
        (created_at BETWEEN :start_date AND :end_date)
";

if ($read_status !== 'all') {
    $sql .= " AND is_read = :is_read";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $db->prepare($sql);

$params = [
    ':start_date' => $start_date,
    ':end_date' => $end_date
];

if ($read_status !== 'all') {
    $params[':is_read'] = ($read_status == 'read') ? 1 : 0;
}

$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_messages = count($messages);
$unread_count = $db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Contact Messages</h5>
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
                <div class="col-md-3 mb-3">
                    <label for="read_status" class="form-label">Status</label>
                    <select class="form-select" id="read_status" name="read_status">
                        <option value="all" <?php echo ($read_status == 'all') ? 'selected' : ''; ?>>All Messages</option>
                        <option value="read" <?php echo ($read_status == 'read') ? 'selected' : ''; ?>>Read</option>
                        <option value="unread" <?php echo ($read_status == 'unread') ? 'selected' : ''; ?>>Unread</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="contact_report.php" class="btn btn-outline-secondary ms-2">Reset</a>
                </div>
            </div>
        </form>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Messages</h6>
                        <h3 class="mb-0"><?php echo $total_messages; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Unread Messages</h6>
                        <h3 class="mb-0"><?php echo $unread_count; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $message): ?>
                    <tr class="<?php echo ($message['is_read'] == 0) ? 'table-warning' : ''; ?>">
                        <td><?php echo $message['id']; ?></td>
                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                        <td><?php echo htmlspecialchars($message['subject']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($message['created_at'])); ?></td>
                        <td>
                            <?php if ($message['is_read'] == 1): ?>
                                <span class="badge bg-success">Read</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Unread</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary view-message" 
                                    data-id="<?php echo $message['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($message['name']); ?>"
                                    data-email="<?php echo htmlspecialchars($message['email']); ?>"
                                    data-phone="<?php echo htmlspecialchars($message['phone']); ?>"
                                    data-subject="<?php echo htmlspecialchars($message['subject']); ?>"
                                    data-message="<?php echo htmlspecialchars($message['message']); ?>"
                                    data-created="<?php echo date('M d, Y h:i A', strtotime($message['created_at'])); ?>"
                                    data-is-read="<?php echo $message['is_read']; ?>">
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

<!-- Message Details Modal -->
<div class="modal fade" id="messageDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message Details - #<span id="modalMessageId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>Contact Information</h6>
                        <p class="mb-1"><strong>Name:</strong> <span id="modalName"></span></p>
                        <p class="mb-1"><strong>Email:</strong> <span id="modalEmail"></span></p>
                        <p class="mb-1"><strong>Phone:</strong> <span id="modalPhone"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Message Details</h6>
                        <p class="mb-1"><strong>Subject:</strong> <span id="modalSubject"></span></p>
                        <p class="mb-1"><strong>Received:</strong> <span id="modalCreated"></span></p>
                        <p class="mb-1"><strong>Status:</strong> <span id="modalStatus" class="badge"></span></p>
                    </div>
                </div>
                <div class="mb-3">
                    <h6>Message Content</h6>
                    <div class="card">
                        <div class="card-body">
                            <p id="modalMessage" class="mb-0"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="markAsReadBtn">
                    <i class="bi bi-check-circle"></i> Mark as Read
                </button>
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
        order: [[4, 'desc']] // Default sort by date descending
    });

    // Current message ID
    let currentMessageId = null;

    // Handle view message button clicks
    $('.view-message').click(function() {
        currentMessageId = $(this).data('id');
        
        // Set modal content
        $('#modalMessageId').text(currentMessageId);
        $('#modalName').text($(this).data('name'));
        $('#modalEmail').text($(this).data('email'));
        $('#modalPhone').text($(this).data('phone'));
        $('#modalSubject').text($(this).data('subject'));
        $('#modalMessage').text($(this).data('message'));
        $('#modalCreated').text($(this).data('created'));
        
        // Set status
        const isRead = $(this).data('is-read');
        const statusBadge = $('#modalStatus');
        statusBadge.removeClass().addClass('badge');
        
        if (isRead == 1) {
            statusBadge.addClass('bg-success').text('Read');
            $('#markAsReadBtn').hide();
        } else {
            statusBadge.addClass('bg-warning').text('Unread');
            $('#markAsReadBtn').show();
        }

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('messageDetailsModal'));
        modal.show();
    });

    // Mark as read button
    $('#markAsReadBtn').click(function() {
        if (currentMessageId) {
            $.post('ajax/mark_message_read.php', { id: currentMessageId }, function(response) {
                if (response.success) {
                    // Update UI
                    $('#modalStatus').removeClass('bg-warning').addClass('bg-success').text('Read');
                    $('#markAsReadBtn').hide();
                    
                    // Update table row
                    $(`.view-message[data-id="${currentMessageId}"]`).closest('tr').removeClass('table-warning');
                    $(`.view-message[data-id="${currentMessageId}"]`).data('is-read', 1);
                    $(`.view-message[data-id="${currentMessageId}"]`).closest('tr').find('.badge')
                        .removeClass('bg-warning').addClass('bg-success').text('Read');
                    
                    // Update unread count
                    const unreadCount = parseInt($('.card-body.text-center h3').last().text());
                    $('.card-body.text-center h3').last().text(unreadCount - 1);
                }
            }, 'json');
        }
    });
});
</script>

<?php
require_once 'includes/admin_footer.php';
?>