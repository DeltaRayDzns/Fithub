<?php
@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if(!isset($admin_id)){
    header('location:login.php');
    exit;
}

$session_message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);

if(isset($_POST['delete_selected'])){
    if(!empty($_POST['message_id'])){
        $ids_to_delete = $_POST['message_id'];
        $placeholders = implode(',', array_fill(0, count($ids_to_delete), '?'));
        
        $delete_message = $conn->prepare("DELETE FROM `message` WHERE id IN ($placeholders)");
        $success = $delete_message->execute($ids_to_delete);
        
        if ($success) {
            $_SESSION['message'] = count($ids_to_delete) . ' messages deleted successfully.';
        } else {
            $_SESSION['message'] = 'Error deleting selected messages.';
        }
        header('location:admin_contacts.php');
        exit;
    } else {
        $_SESSION['message'] = 'No messages selected for deletion.';
        header('location:admin_contacts.php');
        exit;
    }
}

if(isset($_GET['delete'])){
    $delete_id = $_GET['delete'];
    $delete_message = $conn->prepare("DELETE FROM `message` WHERE id = ?");
    $delete_message->execute([$delete_id]);
    
    $_SESSION['message'] = 'Message #' . $delete_id . ' deleted successfully.';
    header('location:admin_contacts.php');
    exit;
}

if(isset($_GET['mark_read_view'])){
    $read_id = $_GET['mark_read_view'];
    
    $update_message = $conn->prepare("UPDATE `message` SET status = 'read' WHERE id = ?");
    $success = $update_message->execute([$read_id]);
    
    if ($success && $update_message->rowCount() > 0) {
        $_SESSION['message'] = 'Message #' . $read_id . ' successfully marked as read.';
        
        header('location:admin_contacts.php#message-' . $read_id);
    } else {
        $_SESSION['message'] = 'Error: Message #' . $read_id . ' could not be marked as read. Check database table or ID.';
        header('location:admin_contacts.php');
    }
    exit;
}

$search_query = isset($_GET['search_box']) ? htmlspecialchars($_GET['search_box']) : '';
$where_clauses = [];
$params = [];

if (!empty($search_query)) {
    $search_term = '%' . $search_query . '%';
    $where_clauses[] = "(id LIKE ? OR name LIKE ? OR user_id LIKE ?)";
    $params = [$search_term, $search_term, $search_term];
}

$where_clause = '';
if (!empty($where_clauses)) {
    $where_clause = " WHERE " . implode(' AND ', $where_clauses);
}

$is_filtered = !empty($search_query);

$status_condition_new = empty($where_clause) ? " WHERE status = 'unread'" : $where_clause . " AND status = 'unread'";
$sql_new = "SELECT * FROM `message`" . $status_condition_new . " ORDER BY created_at DESC"; 
$select_new_message = $conn->prepare($sql_new);
$select_new_message->execute($params);

$status_condition_read = empty($where_clause) ? " WHERE status = 'read'" : $where_clause . " AND status = 'read'";
$sql_read = "SELECT * FROM `message`" . $status_condition_read . " ORDER BY created_at DESC";
$select_read_message = $conn->prepare($sql_read);
$select_read_message->execute($params);

function render_message_section($messages_stmt, $title, $context_class, $header_color, $link_path, $is_filtered, $open_message_id, $section_id) {
    global $conn; 
    $count = $messages_stmt->rowCount();
    $list_item_class = $context_class === 'unread' ? 'list-group-item-unread' : 'list-group-item-read';
    $text_color = $context_class === 'unread' ? 'text-primary' : 'text-muted';
    $delete_btn_class = $context_class === 'unread' ? 'btn-danger' : 'btn-outline-secondary';
    
    $title_class = $context_class === 'unread' ? 'text-indigo' : 'text-secondary';
    ?>
    <h2 class="<?= $title_class; ?> fw-semibold mb-3 mt-5 d-flex align-items-center <?= $section_id === 'read-messages' ? 'justify-content-between' : ''; ?>" id="<?= $section_id; ?>">
        <span class="d-flex align-items-center">
            <i class="<?= $context_class === 'unread' ? 'fas fa-envelope' : 'fas fa-history'; ?> me-2"></i>
            <?= $title; ?> (<?= $count; ?>)
        </span>
        
        <?php if ($section_id === 'read-messages'): ?>
            <span class="d-flex align-items-center" id="bulk-action-controls">
                <span class="text-dark fw-bold small me-2" id="selectedCount">0 Selected</span>
                <button type="submit" name="delete_selected" class="btn btn-sm btn-danger fw-bold btn-square"
                        title="Delete Selected Messages"
                        onclick="return confirm('Are you sure you want to permanently delete the selected messages?');">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </span>
        <?php endif; ?>
    </h2>
    
    <?php if($count > 0): ?>

        <ul class="list-group message-card-container shadow-lg rounded-3 mb-5 border-0">
            
            <li class="list-group-item fw-bold d-none d-md-flex align-items-center rounded-top section-header section-header-<?= $context_class; ?>">
                <div class="form-check me-3" style="width: 3%;">
                    <input class="form-check-input select-all-checkbox" type="checkbox" id="selectAll_<?= $context_class; ?>" data-section="<?= $context_class; ?>" title="Select All in Section">
                </div>
                <div class="text-start flex-shrink-0" style="width: 5%;">ID</div>
                <div class="text-start" style="width: 20%;">Name</div>
                <div class="text-start" style="width: 15%;">User ID</div>
                <div class="text-start" style="width: 27%;">Email</div>
                <div class="text-start" style="width: 15%;">Date/Time</div> 
                <div class="text-end flex-shrink-0" style="width: 15%;">Action</div>
            </li>
            
            <?php while($m = $messages_stmt->fetch(PDO::FETCH_ASSOC)): 
                $is_open = ''; 
                $is_active_class = '';

                $timestamp_raw = $m['created_at'] ?? $m['id']; 
                $timestamp = is_numeric($timestamp_raw) ? $timestamp_raw : strtotime($timestamp_raw);
                $formatted_date = date('Y-m-d h:i A', $timestamp);

                $action_url = ($context_class === 'unread') 
                    ? "admin_contacts.php?mark_read_view=" . $m['id'] 
                    : "#message-" . $m['id']; 
            ?>
                
                <li class="list-group-item list-group-item-action <?= $list_item_class; ?> position-relative p-0 border-0 <?= $is_active_class; ?>">
                    
                    <div class="list-item-summary-link summary-<?= $context_class; ?> d-flex"
                             data-bs-toggle="collapse"
                             data-bs-target="#message-<?= $m['id']; ?>"
                             aria-expanded="<?= !empty($is_open) ? 'true' : 'false'; ?>"
                             aria-controls="message-<?= $m['id']; ?>">
                        
                        <div class="d-flex flex-column flex-md-row align-items-md-center flex-grow-1 w-100 w-md-auto">
                            
                            <div class="form-check me-3 d-none d-md-block" style="width: 3%;">
                                <input class="form-check-input message-checkbox" type="checkbox" name="message_id[]" value="<?= $m['id']; ?>" id="msg_<?= $m['id']; ?>" data-section="<?= $context_class; ?>" onclick="event.stopPropagation();">
                            </div>

                            <div class="fw-bold <?= $text_color; ?> flex-shrink-0 me-md-4 mb-1 mb-md-0" style="width: 5%;">
                                <div class="d-flex align-items-center">
                                    <div class="form-check d-block d-md-none me-2">
                                        <input class="form-check-input message-checkbox" type="checkbox" name="message_id[]" value="<?= $m['id']; ?>" id="msg_mobile_<?= $m['id']; ?>" data-section="<?= $context_class; ?>" onclick="event.stopPropagation();">
                                    </div>
                                    <span class="d-md-none me-2 text-muted fw-normal">ID:</span>#<?= $m['id']; ?>
                                </div>
                            </div>
                            
                            <div class="flex-grow-1 me-md-4 mb-2 mb-md-0" style="width: 20%;">
                                <div class="fw-semibold text-truncate"><?= htmlspecialchars($m['name']); ?></div>
                                
                                <div class="message-details-mobile d-none small text-muted">
                                    <span><i class="fas fa-phone me-1"></i> <?= htmlspecialchars($m['number']); ?></span><br>
                                    <span><i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($m['email']); ?></span><br>
                                    <span class="d-md-none"><i class="fas fa-clock me-1"></i> <?= $formatted_date; ?></span>
                                </div>
                            </div>
                            
                            <div class="text-truncate small d-none d-md-block me-md-4" style="width: 15%;"><?= htmlspecialchars($m['user_id']); ?></div>

                            <div class="text-truncate small d-none d-md-block me-md-4 text-muted" style="width: 27%;">
                                <i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($m['email']); ?>
                            </div>

                            <div class="text-truncate small d-none d-md-block me-md-4 text-secondary" style="width: 15%;">
                                <i class="fas fa-clock me-1"></i> <?= $formatted_date; ?>
                            </div>
                            
                            <div class="d-none d-md-block flex-shrink-0 me-4">
                                <i class="fas fa-chevron-down toggle-icon"></i>
                            </div>
                        </div>
                        
                        <div class="text-end flex-shrink-0 pt-2 pt-md-0 action-wrapper" style="width: 15%;">
                            <?php if ($context_class === 'unread'): ?>
                                <a href="admin_contacts.php?mark_read_view=<?= $m['id']; ?>" 
                                   class="btn btn-sm btn-success me-2 action-mark-read" title="Mark as Read & View" onclick="event.stopPropagation();">
                                    <i class="fas fa-eye me-1"></i> <span class="d-none d-md-inline">View</span>
                                </a>
                            <?php endif; ?>
                            
                            <a href="admin_contacts.php?delete=<?= $m['id']; ?>" 
                               onclick="event.stopPropagation(); return confirm('Archive message #<?= $m['id']; ?> permanently?');" 
                               class="btn btn-sm <?= $delete_btn_class; ?> action-delete" id="btn-delete" title="Archive Message">
                                <i class="fas fa-trash"></i>
                                <span class="d-none d-md-inline ms-1"></span>
                            </a>
                        </div>
                    </div>

                    <div class="collapse message-details-collapse pt-3 ps-4 pe-4 pb-3 border-top border-light-subtle <?= $is_open; ?>" id="message-<?= $m['id']; ?>">
                        
                        <div class="row small mb-3 text-dark">
                             <div class="col-12 col-md-4 mb-2"><strong>ID:</strong> #<?= $m['id']; ?></div>
                            <div class="col-12 col-md-4 mb-2"><strong><i class="fas fa-user-tag me-2"></i>User ID:</strong> <?= htmlspecialchars($m['user_id']); ?></div>
                            <div class="col-12 col-md-4 mb-2"><strong><i class="fas fa-phone me-2"></i>Number:</strong> <?= htmlspecialchars($m['number']); ?></div>
                            <div class="col-12 mt-2"><strong><i class="fas fa-calendar-alt me-2"></i>Sent On:</strong> <?= $formatted_date; ?></div>
                        </div>

                        <div class="alert alert-details shadow-sm p-4 mt-2">
                            <strong class="text-dark"><i class="fas fa-comment-dots me-2"></i>Full Message:</strong> 
                            <p class="mt-2 mb-0 fst-italic text-secondary" style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($m['message'])); ?></p>
                        </div>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>

    <?php elseif ($is_filtered): ?>
        <div class="card shadow-sm p-4 text-center mb-5 card-empty">
            <p class="text-muted mb-0"><i class="fas fa-filter me-2"></i>No messages found matching your search query.</p>
        </div>
    <?php else: ?>
        <div class="card shadow-sm p-4 text-center mb-5 card-empty">
            <p class="text-muted mb-0"><i class="fas fa-check-circle me-2"></i>
                <?= $context_class === 'unread' ? "You're all caught up! No new messages." : "The archive is empty."; ?>
            </p>
        </div>
    <?php endif; ?>
<?php
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css">
    
    <style>
body {
    background-color: #f0f4f8;
    font-family: 'Inter', sans-serif;
    scroll-behavior: smooth; 
}
.main-content {
    margin: auto;
}
h1.title {
    color: #1F2937;
    letter-spacing: 1px;
}
.text-indigo {
    color: #4338ca !important;
}

.search-outer-container {
    padding: 0 15px;
}
.search-inner-wrapper {
    max-width: 500px; 
}
.search-form {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-radius: 2rem;
}
.btn-reset-compact {
    width: 48px; 
    height: 48px; 
    border-radius: 50% !important; 
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); 
}
.search-input-group .form-control {
    border-radius: 2rem 0 0 2rem !important; 
    height: 48px; 
    border-right: none;
    border-color: #dee2e6;
}
.search-input-group .input-group-text {
    background-color: #4338ca;
    color: white;
    border-radius: 0 2rem 2rem 0 !important; 
    border: 1px solid #4338ca;
    height: 48px;
}

.message-card-container {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); 
    border-radius: 1rem !important;
    overflow: hidden;
}

.section-header {
    background: linear-gradient(90deg, #4338ca, #6366f1); 
    color: #fff;
    padding: 1rem 1.5rem !important;
    font-size: 0.9rem;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #5a54cc;
}
.section-header-read {
    background: linear-gradient(90deg, #6c757d, #adb5bd); 
    border-bottom: 2px solid #5a6067;
}

.list-group-item {
    transition: background-color 0.2s ease, opacity 0.3s ease; 
    padding: 0;
}
.list-group-item:last-child {
    border-bottom: none !important;
}

.list-item-summary-link {
    text-decoration: none !important;
    color: inherit;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    transition: background-color 0.2s ease, border-left 0.2s ease;
    border-left: 5px solid transparent; 
}
.list-group-item-unread {
    background-color: #ffffff;
}
.list-group-item-unread:hover:not(.item-active),
.list-group-item-unread .list-item-summary-link[aria-expanded="false"]:hover {
    background-color: #f3f5f7; 
}
.summary-unread {
    border-left-color: #4338ca; 
}
.summary-unread .action-mark-read {
    background-color: #10b981;
    border-color: #10b981;
}

.list-group-item-read {
    background-color: #fafafa;
    opacity: 0.9;
}
.list-group-item-read:hover:not(.item-active),
.list-group-item-read .list-item-summary-link[aria-expanded="false"]:hover {
    background-color: #f0f4f8;
    opacity: 1;
}
.summary-read {
    border-left-color: #a0aec0;
}

.item-active .list-item-summary-link,
.list-item-summary-link[aria-expanded="true"] {
    background-color: #eef2ff !important; 
    border-bottom: 1px solid #d1d5db;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
}

.toggle-icon {
    transition: transform 0.3s ease;
    color: #4338ca;
}
.list-item-summary-link[aria-expanded="true"] .toggle-icon {
    transform: rotate(180deg);
}

.message-details-collapse {
    background-color: #f7f9fc;
    border-top: 1px dashed #d1d5db !important;
}
.alert-details {
    background-color: #ffffff;
    border: 1px solid #e0e7ff;
    border-radius: 0.5rem;
}

.btn-square {
    width: 30px; 
    height: 30px; 
    padding: 0; 
    display: flex;
    align-items: center;
    justify-content: center;
}

#bulk-action-controls {
    transition: opacity 0.2s ease;
    opacity: 0;
    pointer-events: none; 
    display: flex; 
}

#bulk-action-controls.show {
    opacity: 1;
    pointer-events: auto;
}

@media (max-width: 767.98px) {
    
    .list-item-summary-link {
        flex-direction: column; 
        align-items: flex-start;
        padding-bottom: 0.8rem;
    }

    .message-details-mobile {
        display: block !important;
    }
    
    .action-wrapper {
        position: absolute; 
        top: 0.8rem;
        right: 0.8rem;
    }

    #bulk-action-controls {
        position: fixed;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
        background-color: #fff;
        padding: 10px 15px;
        border-radius: 0.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        opacity: 0;
        display: flex !important;
        width: auto;
    }
    #bulk-action-controls.show {
        opacity: 1;
    }

    .list-item-summary-link > div:first-child {
        flex-direction: column !important;
        align-items: flex-start !important;
        width: 100% !important;
    }

    .action-delete, .action-mark-read {
        padding: 0.4rem 0.6rem !important;
    }
}
    </style>
</head>
<body>

<?php include 'admin_header.php'; ?>

<div class="main-content container py-4">

    <?php if ($session_message): ?>
        <div class="alert alert-info alert-dismissible fade show text-center shadow-sm" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <?= htmlspecialchars($session_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>


    <h1 class="title text-center fw-bold mb-4">Customer Messages</h1>

    <div class="d-flex justify-content-center mb-5 search-outer-container">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-center w-100 search-inner-wrapper">
            
            <form action="" method="GET" class="search-form flex-grow-1 w-100 <?= $is_filtered ? 'me-md-3' : ''; ?>">
                <div class="input-group search-input-group">
                    <input type="text" name="search_box" id="search-input" value="<?= htmlspecialchars($search_query); ?>" 
                           class="form-control" 
                           placeholder="Search by Message ID, Name, or User ID..." autofocus>
                    <button type="submit" name="search_btn" class="input-group-text btn-primary" title="Search Messages">
                        <i class="fas fa-search me-1"></i> <span class="d-none d-md-inline">Search</span>
                    </button>
                </div>
            </form>

            <?php if ($is_filtered): ?>
            <a href="admin_contacts.php" class="btn btn-danger mt-3 mt-md-0 ms-md-3 btn-reset-compact" 
                title="Reset Search">
                <i class="fas fa-undo"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <form method="POST" action="admin_contacts.php" id="bulkActionForm">
        
        <?php
        render_message_section(
            $select_new_message, 
            'New Messages', 
            'unread', 
            'primary', 
            'admin_contacts.php?mark_read_view=', 
            $is_filtered, 
            null, 
            'new-messages'
        );
        ?>

        <?php
        render_message_section(
            $select_read_message, 
            'Read Messages', 
            'read', 
            'secondary',
            '#message-', 
            $is_filtered, 
            null, 
            'read-messages'
        );
        ?>
    </form>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const bulkActionControls = document.getElementById('bulk-action-controls');
    const selectedCountSpan = document.getElementById('selectedCount');
    const messageCheckboxes = document.querySelectorAll('.message-checkbox');
    const selectAllCheckboxes = document.querySelectorAll('.select-all-checkbox');

    function updateBulkActionUI() {
        const checkedBoxes = document.querySelectorAll('.message-checkbox:checked');
        const count = checkedBoxes.length;
        
        selectedCountSpan.textContent = `${count} Selected`;

        if (count > 0) {
            bulkActionControls.classList.add('show');
        } else {
            bulkActionControls.classList.remove('show');
        }

        selectAllCheckboxes.forEach(selectAll => {
            const section = selectAll.dataset.section;
            const sectionCheckboxes = document.querySelectorAll(`.message-checkbox[data-section="${section}"]`);
            const sectionChecked = document.querySelectorAll(`.message-checkbox[data-section="${section}"]:checked`);
            
            selectAll.checked = sectionCheckboxes.length > 0 && sectionCheckboxes.length === sectionChecked.length;
            selectAll.indeterminate = sectionChecked.length > 0 && sectionChecked.length < sectionCheckboxes.length;
        });
    }

    messageCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionUI);
    });

    selectAllCheckboxes.forEach(selectAll => {
        selectAll.addEventListener('change', (event) => {
            const section = event.target.dataset.section;
            const isChecked = event.target.checked;
            
            document.querySelectorAll(`.message-checkbox[data-section="${section}"]`).forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateBulkActionUI();
        });
    });

    messageCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    });
    
    updateBulkActionUI();


    window.onload = function() {
        const fragmentId = window.location.hash;

        if (fragmentId && fragmentId.startsWith('#message-')) {
            const msgId = fragmentId.substring(1); 
            const collapseElement = document.getElementById(msgId);
            const listItem = collapseElement ? collapseElement.closest('li.list-group-item') : null;
            const summaryLink = listItem ? listItem.querySelector('.list-item-summary-link') : null;

            if (collapseElement && summaryLink) {
                const collapse = new bootstrap.Collapse(collapseElement, {
                    toggle: false
                });
                collapse.show();

                listItem.classList.add('item-active');
                summaryLink.setAttribute('aria-expanded', 'true');
                
                setTimeout(() => {
                    collapseElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
        }
    };

});
</script>

<script src="js/admin_script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>