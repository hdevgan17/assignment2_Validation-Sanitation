<?php

/*******w******** 
    
    Name: Harshdeep Devgan
    Date: 27 May, 2024
    Description: server-side validation for project 3
                 Generating an invoice

****************/

// Function to validate postal code using regex
function validate_postal_code($postal_code){
    return preg_match('/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/', $postal_code);
}


// Validate form data
$errors = [];
$order = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Customer details
    $name = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $province = filter_input(INPUT_POST, 'province', FILTER_SANITIZE_STRING);
    $postal_code = filter_input(INPUT_POST, 'postal', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $cc_number = filter_input(INPUT_POST, 'cardnumber', FILTER_SANITIZE_NUMBER_INT);
    $cc_month = filter_input(INPUT_POST, 'month', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]]);
    $cc_year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT);
    $cc_type = filter_input(INPUT_POST, 'cardtype', FILTER_SANITIZE_STRING);
    $cc_name = filter_input(INPUT_POST, 'cardname', FILTER_SANITIZE_STRING);
}

// Validate customer  details
if (!$name) $errors[] = "Name is required.";
if (!$address) $errors[] = "Address is required.";
if (!$city) $errors[] = "City is required.";
if (!in_array($province, ['AB', 'BC', 'MB', 'NB', 'NL', 'NS', 'ON', 'PE', 'QC', 'SK'])) $errors[] = "Province is invalid.";
if (!validate_postal_code($postal_code)) $errors[] = "Postal code is invalid.";
if (!$email) $errors[] = "Email is invalid.";
if (!preg_match('/^\d{10}$/', $cc_number)) $errors[] = "Credit card number is invalid.";
$current_month = date('m');
if (!$cc_month || $cc_month < $current_month) $errors[] = "Credit card month is invalid.";
$current_year = date('Y');
if (!$cc_year || $cc_year < $current_year || $cc_year > $current_year + 5) $errors[] = "Credit card year is invalid.";
if (!$cc_name) $errors[] = "Name on card is required.";
if ($cc_type !== 'on') $errors[] = "Credit card type is required.";

// Array of items and their costs
$items = [
    1 => ['name' => 'Macbook', 'price' => 1899.99],
    2 => ['name' => 'Razer Gaming Mouse', 'price' => 79.99],
    3 => ['name' => 'WD My Passport Portable HDD', 'price' => 179.99],
    4 => ['name' => 'Google Nexus 7', 'price' => 249.99],
    5 => ['name' => 'Yamaha DD-45 Drums', 'price' => 119.99]
];

// Validate cart items
for ($i = 1; $i <= 5; $i++) {
    $quantity = filter_input(INPUT_POST, "qty$i", FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($quantity) {
        $order[] = [
            'description' => $items[$i]['name'],
            'quantity' => $quantity,
            'price' => $items[$i]['price']
        ];
    }
}


if (empty($order)) {
    $errors[] = "No valid items in the cart.";
}


?>

<?php if(empty($errors)): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main.css">
    <title>Thanks for your order!</title>
</head>
<body>
    <!-- Remember that alternative syntax is good and html inside php is bad -->
    <div class="invoice">
        <h2>Thanks for your order <?= htmlspecialchars($name) ?>.</h2>
        <h3>Here's a summary of your order:</h3>

        <table>
            <tr>
                <td colspan="4"><h3>Address Information</h3>
                </td>
            </tr>

            <tr>
                <td class="alignright">
                    <span class="bold">Address:</span>
                </td>
                <td>
                    <?= htmlspecialchars($address) ?>
                </td>
                <td class="alignright">
                    <span class="bold">City:</span>
                </td>
                <td>
                    <?= htmlspecialchars($city) ?>
                </td>
            </tr>

            <tr>
                <td class="alignright">
                    <span class="bold">Province:</span>
                </td>
                <td>
                    <?= htmlspecialchars($province) ?>
                </td>
                <td class="alignright">
                    <span class="bold">Postal Code:</span>
                </td>
                <td>
                    <?= htmlspecialchars($postal_code) ?>
                </td>
            </tr>

            <tr>
                <td colspan="2" class="alignright">
                    <span class="bold">Email:</span>
                </td>
                <td colspan="2">
                    <?= htmlspecialchars($email) ?>
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td colspan="3">
                    <h3>Order Information</h3>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="bold">Quantity</span>
                </td>
                <td>
                    <span class="bold">Description</span>
                </td>
                <td>
                    <span class="bold">Cost</span>
                </td>
            </tr>

            <?php
                $total = 0;
                foreach ($order as $item) :
                    $cost = $item['quantity'] * $item['price'];
                    $total += $cost;
            ?>
            <tr>
                <td>
                    <?= $item['quantity'] ?></td><td><?= $item['description'] ?>
                </td>
                <td class="alignright">
                    <?= number_format($cost, 2) ?>
                </td>
            </tr>

            <?php
                endforeach;
            ?>

            <tr>
                <td colspan="2" class="alignright"><span class="bold">
                    Totals</span>
                </td>
                    
                <td class="alignright">
                    <!-- Number formatting with 2 decimal places-->
                    <span class="bold">$ <?= number_format($total, 2) ?></span>
                </td>
            </tr>

        </table>

    </div>
</body>
</html>

<?php
//Page to show errors when the form is not submitted
    else :
        // Display errors
?>
<h2>Form could not be processed due to the following errors:</h2>

<ul>
<?php
    foreach ($errors as $error) :
?>
<li><?= $error ?></li>
<?php
        endforeach;
?>
</ul>
<?php
    endif;
?>