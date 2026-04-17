<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
    header('location:login.php');
    exit; 
};

$select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

$current_address_parts = explode(', ', $fetch_profile['address']);

$current_region = isset($current_address_parts[0]) ? trim($current_address_parts[0]) : '';
$current_province = isset($current_address_parts[1]) ? trim($current_address_parts[1]) : '';
$current_city = isset($current_address_parts[2]) ? trim($current_address_parts[2]) : '';
$current_barangay = isset($current_address_parts[3]) ? trim($current_address_parts[3]) : '';
$current_street = isset($current_address_parts[4]) ? trim($current_address_parts[4]) : '';

if(isset($_POST['update_profile'])){

    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);

    $update_profile = $conn->prepare("UPDATE `users` SET name = ?, email = ?, number = ? WHERE id = ?");
    $update_profile->execute([$name, $email, $number, $user_id]);

    if(isset($_POST['region'])){
        $region = filter_var($_POST['region'], FILTER_SANITIZE_STRING);
        $province = filter_var($_POST['province'], FILTER_SANITIZE_STRING);
        $city = filter_var($_POST['city'], FILTER_SANITIZE_STRING);
        $barangay = filter_var($_POST['barangay'], FILTER_SANITIZE_STRING);
        $street = filter_var($_POST['street'], FILTER_SANITIZE_STRING);
        
        $full_address = $region . ', ' . $province . ', ' . $city . ', ' . $barangay . ', ' . $street;
        
        $update_address = $conn->prepare("UPDATE `users` SET address = ? WHERE id = ?");
        $update_address->execute([$full_address, $user_id]);
        $message[] = 'Profile and Address updated successfully!';
    } else {
        $message[] = 'Profile updated successfully!';
    }


    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/'.$image;
    $old_image = $_POST['old_image'];

    if(!empty($image)){
        if($image_size > 2000000){
            $message[] = 'image size is too large!';
        }else{
            $update_image = $conn->prepare("UPDATE `users` SET image = ? WHERE id = ?");
            $update_image->execute([$image, $user_id]);
            if($update_image){
                move_uploaded_file($image_tmp_name, $image_folder);
                unlink('uploaded_img/'.$old_image);
                $message[] = 'image updated successfully!';
            };
        };
    };

    $select_current_pass = $conn->prepare("SELECT password FROM `users` WHERE id = ?");
    $select_current_pass->execute([$user_id]);
    $current_hash = $select_current_pass->fetchColumn();

    $update_pass = $_POST['update_pass'];
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    if(!empty($update_pass) OR !empty($new_pass) OR !empty($confirm_pass)){
        
        $is_old_md5 = (strlen($current_hash) === 32 && !str_starts_with($current_hash, '$'));
        
        if($is_old_md5 ? (md5($update_pass) != $current_hash) : (!password_verify($update_pass, $current_hash))){
            $message[] = 'Old password not matched!';
        }elseif($new_pass != $confirm_pass){
            $message[] = 'Confirm password not matched!';
        }else{
            if(!empty($new_pass)){
                $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                
                $update_pass_query = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
                $update_pass_query->execute([$hashed_new_pass, $user_id]);
                $message[] = 'Password updated successfully!';
            } else {
                $message[] = 'New password cannot be empty!';
            }
        }
    }
    
    $select_profile->execute([$user_id]);
    $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
    $current_address_parts = explode(', ', $fetch_profile['address']);
    $current_region = isset($current_address_parts[0]) ? trim($current_address_parts[0]) : '';
    $current_province = isset($current_address_parts[1]) ? trim($current_address_parts[1]) : '';
    $current_city = isset($current_address_parts[2]) ? trim($current_address_parts[2]) : '';
    $current_barangay = isset($current_address_parts[3]) ? trim($current_address_parts[3]) : '';
    $current_street = isset($current_address_parts[4]) ? trim($current_address_parts[4]) : '';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>update user profile</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="css/components.css">

</head>
<body>
    
<?php include 'header.php'; ?>

<section class="update-profile">

    <h1 class="title">update profile</h1>

    <?php 
    if(isset($message)){
        foreach($message as $msg){
            echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                      <i class="fas fa-exclamation-circle me-2"></i> 
                      ' . $msg . '
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        }
    }
    ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="">
        
        <div class="flex">
            <div class="inputBox">
                <span>Username :</span>
                <input type="text" name="name" value="<?= $fetch_profile['name']; ?>" placeholder="Update username" required class="box">
                <span>Email Address:</span>
                <input type="email" name="email" value="<?= $fetch_profile['email']; ?>" placeholder="Update email address" required class="box">
                <span>Mobile Number:</span>
                <input type="number" name="number" value="<?= $fetch_profile['number']; ?>" placeholder="Update mobile number" required class="box">
                <span>Update Pic :</span>
                <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box">
                <input type="hidden" name="old_image" value="<?= $fetch_profile['image']; ?>">
            </div>
            
            <div class="inputBox">
                <input type="hidden" name="old_pass" value="<?= $fetch_profile['password']; ?>">
                <span>Old Password :</span>
                <input type="password" name="update_pass" placeholder="Enter previous password" class="box">
                <span>New Password :</span>
                <input type="password" name="new_pass" placeholder="Enter new password" class="box">
                <span>Confirm Password :</span>
                <input type="password" name="confirm_pass" placeholder="Confirm new password" class="box">
            </div>
        </div>

        <h2 class="title mt-5">Update Address</h2>
        <div class="flex-btn mb-4">
            <button type="button" id="edit-address-btn" class="option-btn">Edit Address</button>
            <button type="button" id="cancel-address-btn" class="delete-btn" style="display:none;">Cancel Edit</button>
        </div>

        <div class="flex" id="address-fields">
            <div class="inputBox">
                <span>Region :</span>
                <select id="region" name="region" class="box" required disabled>
                    <option value="">Select Region</option>
                    <?php if($current_region): ?><option value="<?= $current_region; ?>" selected><?= $current_region; ?></option><?php endif; ?>
                </select>
                
                <span>Province :</span>
                <select id="province" name="province" class="box" required disabled>
                    <option value="">Select Province</option>
                    <?php if($current_province): ?><option value="<?= $current_province; ?>" selected><?= $current_province; ?></option><?php endif; ?>
                </select>

                <span>City / Municipality :</span>
                <select id="city" name="city" class="box" required disabled>
                    <option value="">Select City / Municipality</option>
                    <?php if($current_city): ?><option value="<?= $current_city; ?>" selected><?= $current_city; ?></option><?php endif; ?>
                </select>
           </div>


            <div class="inputBox">                   
                <span>Barangay :</span>
                <select id="barangay" name="barangay" class="box" required disabled>
                    <option value="">Select Barangay</option>
                    <?php if($current_barangay): ?><option value="<?= $current_barangay; ?>" selected><?= $current_barangay; ?></option><?php endif; ?>
                </select>
                <span>House No. / Street :</span>
                <input type="text" name="street" id="street" value="<?= $current_street; ?>" placeholder="Enter house no. / street" required class="box" disabled>
            </div>
        </div>

        <div class="flex-btn mt-5">
            <input type="submit" class="btn" value="update profile" name="update_profile">
            <a href="index.php" class="option-btn">go back</a>
        </div>
    </form>

</section>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const regionSelect = document.getElementById('region');
const provinceSelect = document.getElementById('province');
const citySelect = document.getElementById('city');
const barangaySelect = document.getElementById('barangay');
const streetInput = document.getElementById('street');
const editBtn = document.getElementById('edit-address-btn');
const cancelBtn = document.getElementById('cancel-address-btn');

const BASE_URL = "https://psgc.cloud/api/";

const initialRegion = "<?= $current_region; ?>";
const initialProvince = "<?= $current_province; ?>";
const initialCity = "<?= $current_city; ?>";
const initialBarangay = "<?= $current_barangay; ?>";


async function fetchData(endpoint) {
    try {
        const res = await fetch(`${BASE_URL}${endpoint}`);
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        return await res.json();
    } catch (error) {
        console.error("Error fetching data from PSGC API:", error);
        return [];
    }
}

function setAddressFieldsDisabled(disabled) {
    regionSelect.disabled = disabled;
    provinceSelect.disabled = disabled;
    citySelect.disabled = disabled;
    barangaySelect.disabled = disabled;
    streetInput.disabled = disabled;
    
    if (disabled && initialRegion.includes('National Capital Region')) {
        provinceSelect.disabled = true;
    }
}

async function loadRegions(initialLoad = false) {
    const data = await fetchData('regions');
    let regionCode = '';

    if (!initialLoad || !initialRegion) {
        regionSelect.innerHTML = '<option value="">Select Region</option>';
        provinceSelect.innerHTML = '<option value="">Select Province</option>';
        citySelect.innerHTML = '<option value="">Select City / Municipality</option>';
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
    }
    
    data.forEach(r => {
        const opt = document.createElement('option');
        opt.value = r.name;
        opt.textContent = r.name;
        opt.dataset.code = r.code;
        if (r.name === initialRegion) {
            opt.selected = true;
            regionCode = r.code;
        }
        regionSelect.appendChild(opt);
    });
    
    if (regionCode && initialLoad) {
        if (initialRegion.includes('National Capital Region')) {
            loadCitiesByRegion(regionCode, initialCity, initialBarangay, initialLoad);
        } else {
            loadProvinces(regionCode, initialProvince, initialCity, initialBarangay, initialLoad);
        }
    }
}

async function loadProvinces(regionCode, initialVal = '', initialCityVal = '', initialBarangayVal = '', initialLoad = false) {
    if (!initialLoad || !initialVal) {
        provinceSelect.innerHTML = '<option value="">Select Province</option>';
        citySelect.innerHTML = '<option value="">Select City / Municipality</option>';
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
    }
    provinceSelect.disabled = streetInput.disabled;

    if (!regionCode) return;

    const data = await fetchData(`regions/${regionCode}/provinces`);
    let provinceCode = '';
    
    data.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.name;
        opt.textContent = p.name;
        opt.dataset.code = p.code;
        if (p.name === initialVal) {
            opt.selected = true;
            provinceCode = p.code;
        }
        provinceSelect.appendChild(opt);
    });
    
    if (provinceCode && initialLoad) {
        loadCitiesAndMunicipalities(provinceCode, initialCityVal, initialBarangayVal, initialLoad);
    }
}


async function loadCitiesByRegion(regionCode, initialVal = '', initialBarangayVal = '', initialLoad = false) {
    if (!initialLoad || !initialVal) {
        citySelect.innerHTML = '<option value="">Select City / Municipality</option>';
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
    }
    provinceSelect.disabled = true;

    if (!regionCode) return;

    const allCitiesAndMuni = await fetchData(`regions/${regionCode}/cities-municipalities`);
    
    let cityCode = '';

    allCitiesAndMuni.sort((a, b) => a.name.localeCompare(b.name)).forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.name;
        opt.textContent = c.name;
        opt.dataset.code = c.code;
        if (c.name === initialVal) {
            opt.selected = true;
            cityCode = c.code;
        }
        citySelect.appendChild(opt);
    });
    
    if (cityCode && initialLoad) {
        loadBarangays(cityCode, initialBarangayVal, initialLoad);
    }
}


async function loadCitiesAndMunicipalities(provinceCode, initialVal = '', initialBarangayVal = '', initialLoad = false) {
    if (!initialLoad || !initialVal) {
        citySelect.innerHTML = '<option value="">Select City / Municipality</option>';
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
    }

    if (!provinceCode) return;

    const cities = await fetchData(`provinces/${provinceCode}/cities`);
    const municipalities = await fetchData(`provinces/${provinceCode}/municipalities`);
    
    const allCitiesAndMuni = [...cities, ...municipalities].sort((a, b) => a.name.localeCompare(b.name));
    let cityCode = '';

    allCitiesAndMuni.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.name;
        opt.textContent = c.name;
        opt.dataset.code = c.code;
        if (c.name === initialVal) {
            opt.selected = true;
            cityCode = c.code;
        }
        citySelect.appendChild(opt);
    });
    
    if (cityCode && initialLoad) {
        loadBarangays(cityCode, initialBarangayVal, initialLoad);
    }
}

async function loadBarangays(cityCode, initialVal = '', initialLoad = false) {
    if (!initialLoad || !initialVal) {
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
    }

    if (!cityCode) return;

    const data = await fetchData(`cities-municipalities/${cityCode}/barangays`);
    
    data.sort((a, b) => a.name.localeCompare(b.name)).forEach(b => {
        const opt = document.createElement('option');
        opt.value = b.name;
        opt.textContent = b.name;
        if (b.name === initialVal) {
            opt.selected = true;
        }
        barangaySelect.appendChild(opt);
    });
}

editBtn.addEventListener('click', () => {
    setAddressFieldsDisabled(false);
    editBtn.style.display = 'none';
    cancelBtn.style.display = 'inline-block';
    
    const selectedRegionCode = regionSelect.options[regionSelect.selectedIndex]?.dataset.code;
    const regionName = regionSelect.value;
    
    if (regionName.includes('National Capital Region')) {
        provinceSelect.disabled = true;
    } else {
        provinceSelect.disabled = false;
        if (selectedRegionCode) {
            loadProvinces(selectedRegionCode, provinceSelect.value, citySelect.value, barangaySelect.value, false);
        }
    }
});

cancelBtn.addEventListener('click', () => {
    window.location.reload();
});


regionSelect.addEventListener('change', () => {
    const selectedOption = regionSelect.options[regionSelect.selectedIndex];
    const regionCode = selectedOption.dataset.code;
    const regionName = selectedOption.value;

    provinceSelect.innerHTML = '<option value="">Select Province</option>';
    citySelect.innerHTML = '<option value="">Select City / Municipality</option>';
    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

    if (regionName.includes('National Capital Region')) {
        provinceSelect.disabled = true;
        loadCitiesByRegion(regionCode);
    } else {
        provinceSelect.disabled = false;
        loadProvinces(regionCode);
    }
});

provinceSelect.addEventListener('change', () => {
    const selectedOption = provinceSelect.options[provinceSelect.selectedIndex];
    const provinceCode = selectedOption.dataset.code;
    loadCitiesAndMunicipalities(provinceCode);
});

citySelect.addEventListener('change', () => {
    const selectedOption = citySelect.options[citySelect.selectedIndex];
    const cityCode = selectedOption.dataset.code;
    loadBarangays(cityCode);
});

loadRegions(true);
setAddressFieldsDisabled(true);
</script>

</body>
</html>