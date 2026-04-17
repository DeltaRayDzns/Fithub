<?php
include 'config.php';

$name_value = isset($_POST['name']) ? filter_var($_POST['name'], FILTER_SANITIZE_STRING) : '';
$email_value = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_STRING) : '';
$number_value = isset($_POST['number']) ? filter_var($_POST['number'], FILTER_SANITIZE_STRING) : '';
$region_value = isset($_POST['region']) ? filter_var($_POST['region'], FILTER_SANITIZE_STRING) : '';
$province_value = isset($_POST['province']) ? filter_var($_POST['province'], FILTER_SANITIZE_STRING) : '';
$city_value = isset($_POST['city']) ? filter_var($_POST['city'], FILTER_SANITIZE_STRING) : '';
$barangay_value = isset($_POST['barangay']) ? filter_var($_POST['barangay'], FILTER_SANITIZE_STRING) : '';
$street_value = isset($_POST['street']) ? filter_var($_POST['street'], FILTER_SANITIZE_STRING) : '';

if(isset($_POST['submit'])){

    $name = $name_value;
    $email = $email_value;
    $number = $number_value;
    $pass = $_POST['pass'];
    $cpass = $_POST['cpass'];
    $region = $region_value;
    $province = $province_value;
    $city = $city_value;
    $barangay = $barangay_value;
    $street = $street_value;
    
    $full_address = $region . ', ' . $province . ', ' . $city . ', ' . $barangay . ', ' . $street;
    $full_address = filter_var($full_address, FILTER_SANITIZE_STRING);

    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/'.$image;

    $select = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
    $select->execute([$email]);

    if($select->rowCount() > 0){
        $message[] = 'User email already exists!';
    } else {
        // ✅ Password length check
        if(strlen($pass) < 8){
            $message[] = 'Password must be at least 8 characters long!';
        } 
        elseif($pass != $cpass){
            $message[] = 'Confirm password does not match!';
        } 
        elseif($image_size > 2000000){
            $message[] = 'Image size is too large! (Max 2MB)';
        } 
        else {
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            
            $insert_user = $conn->prepare("INSERT INTO `users`(name, number, address, email, password, image) VALUES(?,?,?,?,?,?)");
            $insert_user->execute([$name, $number, $full_address, $email, $hashed_pass, $image]);

            if($insert_user){
                move_uploaded_file($image_tmp_name, $image_folder);
                $message[] = 'Registered successfully!';
                header('location:login.php');
                exit;
            } else {
                $message[] = 'Registration failed due to a database error.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <style>
        body {
            min-height: 100vh; 
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: stretch;
            background-color: #C4DFF5; 
            font-family: 'Rubik', sans-serif;
        }
        .register-container {
            flex-grow: 1;
            display: flex;
            align-items: center;
        }
        .card {
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="container register-container my-5">
        <div class="row justify-content-center w-100">
            <div class="col-md-7 col-lg-6"> 
                <?php if(isset($message)): ?>
                    <?php foreach($message as $msg): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> 
                            <?= $msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">Sign Up</h3>
                        <form id="registerForm" action="" method="POST" enctype="multipart/form-data">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Enter your name" required value="<?= $name_value; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required value="<?= $email_value; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="number" class="form-label">Contact Number</label>
                                <input type="number" name="number" id="number" class="form-control" placeholder="Enter your contact number" required value="<?= $number_value; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="pass" class="form-label">Password</label>
                                <input type="password" name="pass" id="pass" class="form-control" placeholder="Enter your password" minlength="8" required>
                                <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                            </div>
                            <div class="mb-3">
                                <label for="cpass" class="form-label">Confirm Password</label>
                                <input type="password" name="cpass" id="cpass" class="form-control" placeholder="Confirm your password" required>
                                <div class="invalid-feedback">Passwords do not match.</div>
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Profile Image</label>
                                <input type="file" name="image" id="image" class="form-control" required accept="image/jpg, image/jpeg, image/png">
                            </div>

                            <h5 class="mt-4 mb-3">Address Information</h5>

                            <div class="mb-3">
                                <label for="region" class="form-label">Region</label>
                                <select id="region" name="region" class="form-control" required>
                                    <option value="">Select Region</option>
                                    <?php if($region_value): ?><option value="<?= $region_value; ?>" selected><?= $region_value; ?></option><?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="province" class="form-label">Province</label>
                                <select id="province" name="province" class="form-control" required>
                                    <option value="">Select Province</option>
                                    <?php if($province_value): ?><option value="<?= $province_value; ?>" selected><?= $province_value; ?></option><?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="city" class="form-label">City / Municipality</label>
                                <select id="city" name="city" class="form-control" required>
                                    <option value="">Select City / Municipality</option>
                                    <?php if($city_value): ?><option value="<?= $city_value; ?>" selected><?= $city_value; ?></option><?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="barangay" class="form-label">Barangay</label>
                                <select id="barangay" name="barangay" class="form-control" required>
                                    <option value="">Select Barangay</option>
                                    <?php if($barangay_value): ?><option value="<?= $barangay_value; ?>" selected><?= $barangay_value; ?></option><?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="street" class="form-label">House No. / Street</label>
                                <input type="text" name="street" id="street" class="form-control" placeholder="Enter house no. / street" required value="<?= $street_value; ?>">
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" name="submit" class="btn" style="border-radius:20px; height:50px; background-color: #1B80CC; color: #fff;">Register Now</button>
                            </div>
                        </form>

                        <p class="text-center mt-3">
                            Already have an account? <a href="login.php" class="text-decoration-none">Login now</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const regionSelect = document.getElementById('region');
const provinceSelect = document.getElementById('province');
const citySelect = document.getElementById('city');
const barangaySelect = document.getElementById('barangay');

const BASE_URL = "https://psgc.cloud/api/";

const initialRegion = "<?= $region_value; ?>";
const initialProvince = "<?= $province_value; ?>";
const initialCity = "<?= $city_value; ?>";
const initialBarangay = "<?= $barangay_value; ?>";

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

async function loadRegions() {
    const data = await fetchData('regions');
    let regionCode = '';

    regionSelect.innerHTML = '<option value="">Select Region</option>';
    provinceSelect.innerHTML = '<option value="">Select Province</option>';
    citySelect.innerHTML = '<option value="">Select City / Municipality</option>';
    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
    
    provinceSelect.disabled = true;

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
    
    if (regionCode) {
        if (initialRegion.includes('National Capital Region')) {
            loadCitiesByRegion(regionCode, initialCity, initialBarangay);
        } else {
            loadProvinces(regionCode, initialProvince, initialCity, initialBarangay);
        }
    }
}

async function loadProvinces(regionCode, initialVal = '', initialCityVal = '', initialBarangayVal = '') {
    provinceSelect.innerHTML = '<option value="">Select Province</option>';
    citySelect.innerHTML = '<option value="">Select City / Municipality</option>';
    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
    provinceSelect.disabled = false;

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
    
    if (provinceCode) {
        loadCitiesAndMunicipalities(provinceCode, initialCityVal, initialBarangayVal);
    }
}

async function loadCitiesByRegion(regionCode, initialVal = '', initialBarangayVal = '') {
    citySelect.innerHTML = '<option value="">Select City / Municipality</option>';
    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
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
    
    if (cityCode) {
        loadBarangays(cityCode, initialBarangayVal);
    }
}

async function loadCitiesAndMunicipalities(provinceCode, initialVal = '', initialBarangayVal = '') {
    citySelect.innerHTML = '<option value="">Select City / Municipality</option>';
    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

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
    
    if (cityCode) {
        loadBarangays(cityCode, initialBarangayVal);
    }
}

async function loadBarangays(cityCode, initialVal = '') {
    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

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

regionSelect.addEventListener('change', () => {
    const selectedOption = regionSelect.options[regionSelect.selectedIndex];
    const regionCode = selectedOption.dataset.code;
    const regionName = selectedOption.value;

    provinceSelect.innerHTML = '<option value="">Select Province</option>';
    citySelect.innerHTML = '<option value="">Select City / Municipality</option>';
    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';

    if (regionName.includes('National Capital Region')) {
        provinceSelect.disabled = true;
        provinceSelect.value = '';
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

loadRegions();
</script>

<script>
(() => {
    'use strict';

    const form = document.getElementById('registerForm');
    const pass = document.getElementById('pass');
    const cpass = document.getElementById('cpass');

    form.addEventListener('submit', event => {
        pass.classList.remove('is-invalid');
        cpass.classList.remove('is-invalid');

        if (pass.value.length < 8) {
            pass.classList.add('is-invalid');
            event.preventDefault();
            event.stopPropagation();
        }

        if (pass.value !== cpass.value) {
            cpass.classList.add('is-invalid');
            event.preventDefault();
            event.stopPropagation();
        }

        form.classList.add('was-validated');
    });
})();
</script>

</body>
</html>
