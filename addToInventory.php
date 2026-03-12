<?php
// !!!!!!!!!!!!!!!!!!!!!PLEASE COME BACK TO THIS OCDE THE JAVASCRIPT PART, TRY TO PROPERLY UNDERSTAND IT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// NOTES, WITH THE BUTTONS REALLY COME BACK, BECAUSE THE FORM IS TERMINATING BEFORE IT SUBMITS, ADD EVENT LISTENERS!!!!!!!!!!!!!!!!!!! PLEASE DO NOT FORGET

require_once 'config.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header('Location: signin.php');
    exit;
}

$seller_id = $_SESSION['user_id'];

// Get seller's store
$sql_store = "SELECT id FROM stores WHERE seller_id = ?";
$stmt_store = $conn->prepare($sql_store);
$stmt_store->bind_param("i", $seller_id);
$stmt_store->execute();
$result_store = $stmt_store->get_result();
$store = $result_store->fetch_assoc();
$stmt_store->close();

if (!$store) {
    // Seller has no store – redirect to dashboard
    header('Location: sellerDashboard.php?error=no_store');
    exit;
}
$store_id = $store['id'];

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['itemName'] ?? '');
    $price = (float)($_POST['itemPrice'] ?? 0);
    $condition = $_POST['itemCondition'] ?? '';
    $comment = trim($_POST['itemComment'] ?? '');
    $category = trim($_POST['category'] ?? ''); 
    // Validate
    if (empty($name)) $errors[] = "Item name is required.";
    if ($price <= 0) $errors[] = "Price must be greater than zero.";
    if (empty($condition)) $errors[] = "Condition is required.";
    if (empty($comment)) $errors[] = "Description is required.";

    // Handle file upload
    $image_url = null;
    if (!empty($_FILES['images']['name'][0])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $file_name = time() . '_' . basename($_FILES['images']['name'][0]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is actual image
        $check = getimagesize($_FILES['images']['tmp_name'][0]);
        if ($check !== false && in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES['images']['tmp_name'][0], $target_file)) {
                $image_url = $target_file;
            } else {
                $errors[] = "Sorry, there was an error uploading your file.";
            }
        } else {
            $errors[] = "File is not a valid image.";
        }
    } else {
        $errors[] = "At least one image is required.";
    }

    if (empty($errors)) {
        // Insert product
        $sql = "INSERT INTO products (store_id, title, description, price, `condition`, stock_quantity, image_url, category, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stock_quantity = 1; // default stock
        $stmt->bind_param("issdssss", $store_id, $name, $comment, $price, $condition, $stock_quantity, $image_url, $category);
        if ($stmt->execute()) {
            $success = true;
            // Redirect to seller dashboard with message
            header('Location: sellerDashboard.php?msg=product_added');
            exit;
        } else {
            $errors[] = "Failed to add product to database.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css" />
    <style>
        /*  Add to Inventory Page styling */
        body {
            background-color: var(--purple-bg);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        .myFoot {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--secondary);
            padding: 20px;
            margin-bottom: 20px;
        }

        .top h2 {
            color: var(--primary);
            font-family: Cambria, Cochin, Georgia, Times, serif;
            font-size: 2rem;
            margin: 0;
        }

        .bottom p {
            color: var(--text-muted);
            font-size: 1.2rem;
            margin-top: 10px;
        }

        main {
            max-width: 800px;
            margin: 0 auto;
        }

        h1 {
            color: var(--primary);
            font-family: Cambria, Cochin, Georgia, Times, serif;
            font-size: 2rem;
            margin: 30px 0 20px;
            text-align: center;
        }

        .basicInfo {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--secondary);
            padding: 20px;
            margin: 20px 0;
        }

        .addItem {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .addItem label {
            min-width: 120px;
            font-weight: 600;
            color: var(--dark);
        }

        .addItem input,
        .addItem select,
        .addItem textarea {
            flex: 1;
            padding: 12px;
            border: 2px solid var(--secondary);
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .addItem input:focus,
        .addItem select:focus,
        .addItem textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .uploadBox {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: var(--primary);
            color: var(--white);
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s;
            width: 100%;
        }

        .uploadBox:hover {
            background-color: var(--accent);
        }

        .uploadBox input {
            display: none;
        }

        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }

        .preview-item {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .remove-image:hover {
            background: rgba(255, 0, 0, 0.9);
        }

        .myButton {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .submit-btn {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .submit-btn:hover {
            background-color: var(--accent);
        }

        #cancel-button {
            background-color: var(--secondary);
            color: var(--dark);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #cancel-button:hover {
            background-color: #d4b5e6;
        }

        .error-message {
            color: red;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        /* Responsive design for add to inventory page*/
        @media (max-width: 600px) {
            .addItem {
                flex-direction: column;
                align-items: flex-start;
            }
            .addItem label {
                min-width: auto;
            }
            .addItem input,
            .addItem select,
            .addItem textarea {
                width: 100%;
            }
            .myButton {
                flex-direction: column;
                gap: 10px;
            }
            .submit-btn,
            #cancel-button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="myFoot">
            <div class="top">
                <h2>RU Thrifty</h2>
            </div>
            <div class="bottom">
                <p>Add a new item to your inventory</p>
            </div>
        </div>
    </header>

    <main>
        <br />
        <p>Create a new Listing</p>
        <br />
        <h1>Item information</h1>
        <section class="basicInfo">
            <form
                id="listingForm"
                method="post"
                enctype="multipart/form-data"
            >
                <section class="addItem">
                    <label for="itemName">Item Name</label>
                    <input
                        type="text"
                        id="itemName"
                        name="itemName"
                        placeholder="Enter the name of your item"
                        required
                    />
                </section>

                <section class="addItem">
                    <label for="itemPrice">Price (R)</label>
                    <input
                        type="number"
                        id="itemPrice"
                        name="itemPrice"
                        placeholder="Enter the price of the product"
                        min="0"
                        step="0.01"
                        required
                    />
                </section>

                <section class="addItem">
                    <label for="itemCondition">Condition</label>
                    <select id="itemCondition" name="itemCondition" required>
                        <option value="">Select condition</option>
                        <option value="New">New</option>
                        <option value="Like New">Like New</option>
                        <option value="Excellent">Excellent</option>
                        <option value="Good">Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Poor">Poor</option>
                    </select>
                </section>

                <section class="addItem">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" placeholder="e.g., Textbooks, Electronics">
                </section>

                <section class="addItem">
                    <label for="itemComment">General Comments</label>
                    <textarea
                        id="itemComment"
                        name="itemComment"
                        placeholder="Enter additional information about the product"
                        rows="4"
                        required
                    ></textarea>
                </section>

                <!-- Image Upload Section -->
                <h2 style="margin-top:20px;">Add Pictures</h2>
                <label class="uploadBox">
                    Add Images
                    <input
                        type="file"
                        id="imageInput"
                        name="images[]"
                        accept="image/*"
                        multiple
                    />
                </label>

                <!-- Image Preview Container -->
                <div id="previewContainer" class="preview-container"></div>

                <!-- Hidden input to track selected files for form submission -->
                <div id="fileDataContainer"></div>

                <section class="myButton">
                    <button type="submit" class="submit-btn" id="submit-btn">
                        Create Listing
                    </button>
                    <button type="button" class="submit-btn" id="cancel-button">Cancel Listing</button>
                </section>
            </form>
        </section>
    </main>

    <script>
        // Image preview and management functionality
        const imageInput = document.getElementById("imageInput");
        const previewContainer = document.getElementById("previewContainer");
        const fileDataContainer = document.getElementById("fileDataContainer");
        let selectedFiles = [];

        imageInput.addEventListener("change", function (event) {
            const files = Array.from(event.target.files);

            const validFiles = files.filter((file) => {
                const isValidType = file.type.startsWith("image/");
                const isValidSize = file.size <= 5 * 1024 * 1024; // 5MB max

                if (!isValidType) {
                    alert(`${file.name} is not an image file. Please select only images.`);
                    return false;
                }
                if (!isValidSize) {
                    alert(`${file.name} is too large. Maximum size is 5MB.`);
                    return false;
                }
                return true;
            });

            selectedFiles = [...selectedFiles, ...validFiles];
            displayPreviews();
            updateFormData();
            imageInput.value = "";
        });

        function displayPreviews() {
            previewContainer.innerHTML = "";

            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();

                reader.onload = function (e) {
                    const previewItem = document.createElement("div");
                    previewItem.className = "preview-item";
                    previewItem.dataset.index = index;

                    const img = document.createElement("img");
                    img.src = e.target.result;
                    img.alt = `Preview ${index + 1}`;

                    const removeBtn = document.createElement("button");
                    removeBtn.className = "remove-image";
                    removeBtn.innerHTML = "×";
                    removeBtn.onclick = function () {
                        removeImage(index);
                    };

                    previewItem.appendChild(img);
                    previewItem.appendChild(removeBtn);
                    previewContainer.appendChild(previewItem);
                };

                reader.readAsDataURL(file);
            });
        }

        function removeImage(index) {
            selectedFiles.splice(index, 1);
            displayPreviews();
            updateFormData();
        }

        function updateFormData() {
            fileDataContainer.innerHTML = "";
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            imageInput.files = dataTransfer.files;
        }

        // Form submission 
        document.getElementById("listingForm").addEventListener("submit", function (event) {
            if (selectedFiles.length === 0) {
                event.preventDefault();
                alert("Please select at least one image for your listing.");
                return;
            }
           
        });

        // Cancel button
        const cancelButton = document.getElementById("cancel-button");
        cancelButton.onclick = function (event) {
            event.preventDefault();
            if (confirm("Are you sure you want to cancel?")) {
                window.location.href = "sellerDashboard.php";
            }
        };
    </script>
    <script src="index.js" defer></script>
</body>
</html>