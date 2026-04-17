<?php
@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];

$message = [];

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); 
}

if(!isset($admin_id)){
    header('location:login.php');
    exit;
}

if(isset($_GET['delete'])){
    $delete_id = $_GET['delete'];
    
    if ($delete_id == $admin_id) {
        $_SESSION['message'] = ['You cannot delete your own account!'];
        header('location:admin_users.php');
        exit;
    }

    $delete_users = $conn->prepare("DELETE FROM `users` WHERE id = ?");
    $delete_users->execute([$delete_id]);
    
    $_SESSION['message'] = ['User account deleted successfully!'];
    header('location:admin_users.php');
    exit;
}

if(isset($_POST['update_user_type'])){
    $user_id = $_POST['user_id'];
    $new_type = $_POST['user_type'];

    if ($new_type == 'user' || $new_type == 'admin') {
        if ($user_id != $admin_id) {
            $update_type = $conn->prepare("UPDATE `users` SET user_type = ? WHERE id = ?");
            $update_type->execute([$new_type, $user_id]);
            
            $_SESSION['message'] = ['Success! User role has been updated to ' . ucfirst($new_type) . '.']; 
            header('location:admin_users.php');
            exit;
        } else {
            $_SESSION['message'] = ['You cannot change your own user role!']; 
            header('location:admin_users.php');
            exit;
        }
    }
}

$search_query = isset($_GET['search_box']) ? htmlspecialchars($_GET['search_box']) : '';
$where_clauses = [];
$params = [];

if (!empty($search_query)) {
    $search_term = '%' . $search_query . '%';
    $where_clauses[] = "(name LIKE ? OR email LIKE ? OR id LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = '';
if (!empty($where_clauses)) {
    $where_clause = " WHERE " . implode(' AND ', $where_clauses);
}

$is_filtered = !empty($search_query);

$sql = "SELECT * FROM `users`" . $where_clause;
$select_users = $conn->prepare($sql);
$select_users->execute($params);

function calculate_account_age($date_string) {
    if (empty($date_string)) return 'N/A';
    try {
        $now = new DateTime();
        $reg_date = new DateTime($date_string);
        $interval = $now->diff($reg_date);
        
        if ($interval->y > 0) return $interval->y . ' years ago';
        if ($interval->m > 0) return $interval->m . ' months ago';
        if ($interval->d > 0) return $interval->d . ' days ago';
        if ($interval->h > 0) return $interval->h . ' hours ago';
        if ($interval->i > 0) return $interval->i . ' minutes ago';
        return 'Just now';
    } catch (Exception $e) {
        return 'Date Error';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css">

    <style>
body {
    background-color: #f8f9fa;
}
.card {
    border-left: 5px solid var(--bs-primary);
    transition: transform 0.2s, box-shadow 0.2s;
    border-radius: 0.75rem;
}
.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.18) !important;
}
.title {
    color: #343a40;
}
.action-btn {
    width: 80px; 
    height: 45px;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 0.5rem;
    font-size: 1.2rem;
}
.btn-outline-info { --bs-btn-hover-bg: var(--bs-info); --bs-btn-hover-border-color: var(--bs-info); }
.btn-outline-primary { --bs-btn-hover-bg: var(--bs-primary); --bs-btn-hover-border-color: var(--bs-primary); }
.btn-outline-danger { --bs-btn-hover-bg: var(--bs-danger); --bs-btn-hover-border-color: var(--bs-danger); }


.search-outer-container { padding: 0 15px; }
.search-inner-wrapper { max-width: 600px; }
.search-form {
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    border-radius: 2rem;
}
.btn-reset-compact {
    width: 48px; height: 48px; border-radius: 50% !important; 
    display: flex; justify-content: center; align-items: center;
    padding: 0; flex-shrink: 0; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); 
    transition: background-color 0.2s ease;
}
.btn-reset-compact:hover { background-color: #c82333; }
.search-input-group .form-control {
    border-radius: 2rem 0 0 2rem !important; height: 48px; 
    border-right: none; padding: 0.75rem 1.5rem; border-color: #dee2e6;
}
.search-input-group .input-group-text {
    background-color: #007bff; color: white;
    border-radius: 0 2rem 2rem 0 !important; 
    border: 1px solid #007bff; padding: 0 1.5rem;
    height: 48px; font-weight: bold; cursor: pointer;
}

.user-card-content {
    display: flex;
    flex-direction: row; 
    justify-content: space-between;
}
.user-info-wrapper {
    flex-grow: 1;
    display: flex; 
    flex-direction: column;
}
.user-avatar-group {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
}
.user-profile-img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #e0e0e0;
    margin-right: 0.75rem;
    flex-shrink: 0;
}
.user-profile-icon {
    font-size: 2.25rem;
    color: #bdbdbd;
    margin-right: 0.75rem;
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.user-name-id h3 {
    margin-bottom: 0;
    font-size: 1.5rem;
    line-height: 1.3; 
    word-wrap: break-word; 
    white-space: normal;
}
.user-name-id p {
    font-size: 1.2rem;
    color: #6c757d;
    margin-bottom: 0;
}
.user-details {
    font-size: 1rem;
    margin-bottom: 0.75rem;
}
.user-details p {
    margin-bottom: 0.25rem;
}
.user-details strong {
    color: #495057;
    min-width: 60px;
    display: inline-block;
}
.user-details .fa-fw {
    width: 1.25em;
    text-align: center;
}
.user-role {
    padding-top: 0.5rem;
    border-top: 1px solid #eee;
    font-size: 1rem;
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}
.user-role strong {
    color: #495057;
    margin-right: 0.5rem;
}
.user-role-text {
    font-weight: bold;
}
.action-buttons-group {
    display: flex;
    flex-direction: row; 
    gap: 0.5rem;
    align-items: center; 
    justify-content: center; 
    padding-top: 1rem; 
    border-top: 1px solid #eee;
}
    </style>
</head>
<body>

<?php include 'admin_header.php'; ?>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger border-3 rounded-lg shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel"><i class="fas fa-exclamation-triangle me-2"></i> Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                Are you sure you want to permanently delete this user account? This action **cannot be undone**.
            </div>
            <div class="modal-footer justify-content-between">
                <a href="#" id="modalDeleteLink" class="btn btn-danger fw-bold">Confirm</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

            </div>
        </div>
    </div>
</div>
<div class="main-content container-fluid mt-4">

    <?php 
    if (isset($message) && is_array($message)) { 
        foreach ($message as $msg) {
            $is_success = strpos($msg, 'success') !== false || strpos($msg, 'deleted') !== false || strpos($msg, 'updated') !== false;
            $alert_class = $is_success ? 'alert-success' : 'alert-warning';
            $icon_class = $is_success ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';

            echo '<div class="alert ' . $alert_class . ' alert-dismissible fade show text-center" role="alert">
                    <i class="' . $icon_class . ' me-2"></i><strong>' . htmlspecialchars($msg) . '</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        }
    }
    ?>
    <h1 class="title text-center fw-bold mb-4">Manage User Accounts</h1>
    
    <div class="d-flex justify-content-center mb-5 search-outer-container">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-center w-100 search-inner-wrapper">
            
            <form action="" method="GET" class="search-form flex-grow-1 w-100 <?= $is_filtered ? 'me-md-3' : ''; ?>">
                <div class="input-group search-input-group">
                    <input type="text" name="search_box" id="search-input" value="<?= htmlspecialchars($search_query); ?>" 
                            class="form-control" 
                            placeholder="Search by Name, Email, or ID..." autofocus>
                    <button type="submit" name="search_btn" class="input-group-text btn-primary" title="Search Users">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                </div>
            </form>

            <?php if ($is_filtered): ?>
            <a href="admin_users.php" class="btn btn-danger mt-3 mt-md-0 ms-md-3 btn-reset-compact" 
                title="Reset Search">
                <i class="fas fa-undo"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="row g-4 mb-5">

        <?php
        $user_found = false; 
        $user_modals = ''; 

        while($fetch_users = $select_users->fetch(PDO::FETCH_ASSOC)){
            
            if($fetch_users['id'] == $admin_id) continue;

            $user_found = true;
            
            $user_type_color = ($fetch_users['user_type'] == 'admin') ? 'text-danger' : 'text-success';
            $modal_id = 'editRoleModal' . $fetch_users['id'];
            $view_modal_id = 'viewUserModal' . $fetch_users['id'];
            
            $user_modals .= '
                <div class="modal fade" id="' . $modal_id . '" tabindex="-1" aria-labelledby="' . $modal_id . 'Label" aria-hidden="true">
                    <div class="modal-dialog modal-sm modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="' . $modal_id . 'Label">Change Role for ' . htmlspecialchars($fetch_users['name']) . '</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="" method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="user_id" value="' . $fetch_users['id'] . '">
                                    <div class="mb-3">
                                        <label for="user_type_' . $fetch_users['id'] . '" class="form-label small fw-bold">Select New Role:</label>
                                        <select name="user_type" id="user_type_' . $fetch_users['id'] . '" class="form-select">
                                            <option value="user" ' . (($fetch_users['user_type'] == 'user') ? 'selected' : '') . '>User</option>
                                            <option value="admin" ' . (($fetch_users['user_type'] == 'admin') ? 'selected' : '') . '>Admin</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="update_user_type" class="btn btn-primary" id="btn-check"><i class="fas fa-sync-alt me-1"></i> Update Role</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            ';
            
            $user_modals .= '
                <div class="modal fade" id="' . $view_modal_id . '" tabindex="-1" aria-labelledby="' . $view_modal_id . 'Label" aria-hidden="true">
                    <div class="modal-dialog modal-sm modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="' . $view_modal_id . 'Label">User Details: ' . htmlspecialchars($fetch_users['name']) . '</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                ' . (empty($fetch_users['image']) ? 
                                    '<i class="fas fa-user-circle fa-5x text-secondary mb-3"></i>' :
                                    '<img src="uploaded_img/' . $fetch_users['image'] . '" alt="User Profile" 
                                    class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #ddd;">') . '
                                <p class="mb-1"><strong>ID:</strong> ' . htmlspecialchars($fetch_users['id']) . '</p>
                                <p class="mb-1"><strong>Email:</strong> ' . htmlspecialchars($fetch_users['email']) . '</p>
                                <p class="mb-1"><strong>Role:</strong> <span class="fw-bold ' . $user_type_color . '">' . ucfirst($fetch_users['user_type']) . '</span></p>
                                <hr class="my-3">
                                <p class="mb-0 small text-muted">Account Registered:</p>
                                <p class="fw-bold text-primary">' . (isset($fetch_users['registered_at']) ? calculate_account_age($fetch_users['registered_at']) : 'N/A (No registered_at column)') . '</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            ';
        ?>
        
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
            <div class="card shadow-sm p-3 h-100 d-flex">
                <div class="user-card-content d-flex flex-grow-1">
                    <div class="user-info-wrapper flex-grow-1"> 
                        
                        <div class="user-avatar-group">
                            <?php if(!empty($fetch_users['image'])): ?>
                                <img src="uploaded_img/<?= $fetch_users['image']; ?>" alt="User Profile" 
                                    class="user-profile-img">
                            <?php else: ?>
                                <i class="fas fa-user-circle user-profile-icon"></i>
                            <?php endif; ?>
                            <div class="user-name-id flex-grow-1">
                                <h3 class="fw-bold" title="<?= htmlspecialchars($fetch_users['name']); ?>"><?= htmlspecialchars($fetch_users['name']); ?></h3>
                                <p class="mb-0">ID: <?= htmlspecialchars($fetch_users['id']); ?></p>
                            </div>
                        </div>
                        
                        <div class="user-details mb-2">
                            <p class="mb-0"><strong class="me-2"><i class="fas fa-at fa-fw"></i>Email:</strong> <span class="text-truncate" title="<?= htmlspecialchars($fetch_users['email']); ?>"><?= htmlspecialchars($fetch_users['email']); ?></span></p>
                        </div>
                        
                        <div class="user-role mt-auto">
                            <strong><i class="fas fa-user-shield fa-fw me-1"></i>Role:</strong> 
                            <span class="user-role-text <?= $user_type_color; ?>"><?= ucfirst($fetch_users['user_type']); ?></span>
                        </div>
                    
                        <div class="action-buttons-group">
                            <button type="button" class="btn btn-outline-info action-btn" 
                                    data-bs-toggle="modal" data-bs-target="#<?= $view_modal_id; ?>" title="View User Details">
                                <i class="fas fa-eye"></i>
                            </button>

                            <button type="button" class="btn btn-outline-primary action-btn" id="btn-edit"
                                    data-bs-toggle="modal" data-bs-target="#<?= $modal_id; ?>" title="Change User Role">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <button type="button" 
                                class="btn btn-outline-danger action-btn btn-delete-user" id="btn-delete"
                                data-delete-url="admin_users.php?delete=<?= $fetch_users['id']; ?>"
                                title="Delete User">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        </div>
                </div>
            </div>
        </div>

        <?php
        } 

        if (!$user_found) {
            $empty_message = $is_filtered ? 'No user accounts found matching your search query.' : 'No other user accounts found!';
            echo '<div class="col-12">
                        <div class="card shadow-sm p-4 text-center border-0">
                            <p class="empty mb-0 text-muted"><i class="fas fa-filter me-2"></i>' . $empty_message . '</p>
                        </div>
                    </div>';
        }
        ?>

    </div>
    
    
<?= $user_modals; ?>

</div>

<script src="js/admin_script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const deleteButtons = document.querySelectorAll('.btn-delete-user');
    const deleteModalElement = document.getElementById('deleteConfirmModal');
    const modalLink = document.getElementById('modalDeleteLink');
    
    if (deleteModalElement && modalLink) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const deleteUrl = this.getAttribute('data-delete-url');
                
                modalLink.href = deleteUrl;
                
                const deleteModal = new bootstrap.Modal(deleteModalElement);
                deleteModal.show();
            });
        });
    }
});
</script>

</body>
</html>