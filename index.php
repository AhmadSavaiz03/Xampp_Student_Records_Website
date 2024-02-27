<?php
    $server = "localhost";
    $username = "root";
    $password = "";
    $dbname = "students";
    $conn = mysqli_connect($server,$username,$password,$dbname);
    if(!$conn) die("connection to this database failed due to" . mysqli_connect_error());


    // $sql = "INSERT INTO `students`.`students` (`name`, `last_modifed`) VALUES ('a', current_timestamp())";
    // $conn->query($sql);
    // $sql = "INSERT INTO `students`.`students` (`name`, `last_modifed`) VALUES ('b', current_timestamp())";
    // $conn->query($sql);
    // $sql = "INSERT INTO `students`.`students` (`name`, `last_modifed`) VALUES ('c', current_timestamp())";
    // $conn->query($sql);
    // $sql = "INSERT INTO `students`.`students` (`name`, `last_modifed`) VALUES ('d', current_timestamp())";
    // $conn->query($sql);

    if (isset($_GET['sort'])) {
        $sortColumn = $_GET['sort'];
        $sortDirection = isset($_GET['dir']) && strtolower($_GET['dir']) === 'desc' ? 'DESC' : 'ASC';
        
        // Validate the sortColumn to prevent SQL injection
        $allowedColumns = array('serial', 'name', 'dob', 'address', 'mobile');
        if (!in_array($sortColumn, $allowedColumns)) {
            // Default to a valid column if an invalid one is provided
            $sortColumn = 'serial';
        }
    
        $sql = "SELECT * FROM students ORDER BY $sortColumn $sortDirection";
    } else {
        // Default sorting if no sort parameter is provided
        $sortColumn = 'serial';
        $sortDirection = 'ASC';
        $sql = "SELECT * FROM students ORDER BY $sortColumn $sortDirection";
    }

    $result = $conn->query($sql);
    $count = 0;

    if ($result->num_rows > 0){
        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $conn->close();

    // Pagination settings
    $recordsPerPage = 7;
    $totalRecords = count($data);
    $totalPages = ceil($totalRecords / $recordsPerPage);

    // Get the current page from the URL parameter or default to page 1
    if (isset($_GET['page']) && is_numeric($_GET['page'])) {
        $currentPage = intval($_GET['page']);
        if ($currentPage < 1) {
            $currentPage = 1;
        } elseif ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
    } else {
        $currentPage = 1;
    }

    // Calculate the starting index for the current page
    $startIndex = ($currentPage - 1) * $recordsPerPage;

    // Fetch only the records for the current page
    $currentPageData = array_slice($data, $startIndex, $recordsPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <title>User Data</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Manage Users</h1>
        <div class=button-container>
            <button id="new" class="button1"><b>Add New User</b></button>
            <input type="submit" id="del" name="deleteSelected" class="button2" value="Delete Users">
        </div>    
    </div>

    <div class="table-container">
        <h1>Students</h1>
        <?php if (isset($data) && count($data) > 0) { ?>
            <form class="form3" id="form3" method="POST">
            <table>
                <tr>
                    <th colspan="7"><h2>Student Details</h2></th>
                </tr>
                <tr>
                    <th></th>
                    <th class="sortable" data-column="SR"><a href="?sort=serial"> SR</th>
                    <th class="sortable" data-column="Name"><a href="?sort=name"> Name</th>                
                    <th class="sortable" data-column="Birth"><a href="?sort=dob"> Birth</th>
                    <th class="sortable" data-column="Address"><a href="?sort=address"> Address</th>
                    <th class="sortable" data-column="Number"><a href="?sort=mobile"> Number</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($currentPageData as $row) { ?>
                    <tr>
                        <td><input type="checkbox" name="selectedStudents[]" value="<?php echo $row['serial']; ?>"></td>
                        <td><?php echo ++$count + ($currentPage-1)*5; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['dob']; ?></td>
                        <td><?php echo $row['address']; ?></td>
                        <td><?php echo $row['mobile']; ?></td>
                        <td>
                            <form class="form3" id="form" method="POST">
                                <div class="button-container2">
                                <input type="hidden" name="serial" value="<?php echo $row['serial']; ?>">
                                <input type="submit" class="button3 editClick" name="edit" id="edit" value=""></input>
                                <input type="submit" class="button4" name="delete" value=""></input>
                                </div>
                            </form>

                        </td>
                    </tr>
                <?php } ?>
                </table>
                <b><input type="submit" name="deleteSelected" class="button2" value="Delete Users"></b>
        <?php } ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                <?php if ($i === $currentPage) { ?>
                    <span class="page-link current"><?php echo $i; ?></span>
                <?php } else { ?>
                    <a class="page-link" href="?page=<?php echo $i; ?>&sort=<?php echo $sortColumn; ?>&dir=<?php echo $sortDirection; ?>"><?php echo $i; ?></a>
                <?php } ?>
            <?php } ?>
        </div>
    </div>

    <div id="overlay" class="overlay"></div>
    <div id="popup" class="popup">
        <h1>Add New Student</h1>
        <form class="form" id="form" method="POST" enctype="multipart/form-data">
            <label for="name">Name:</label>
            <input type="text" name="name" id="name" required><br><br>
            <label for="dob">Date of Birth:</label>
            <input type="date" name="dob" id="dob" ><br><br>
            <label for="pic">Profile Picture:</label>
            <input type="file" name="pic" id="pic" ><br><br>
            <label for="address">Address:</label>
            <textarea name="address" id="address" required></textarea><br><br>
            <label for="mobile">Mobile Number:</label>
            <input type="tel" name="mobile" id="mobile" required><br><br>
            <input type="submit" name="submit" value="Add">
        </form>
    </div>

    <div id="overlay2" class="overlay2"></div>
    <?php foreach ($data as $row) { ?>
        <div id="popup_edit_<?php echo $row['serial']; ?>" class="popup2" style="display: none;">
            <h1>Edit Student</h1>
            <form class="form2" method="POST" enctype="multipart/form-data">
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($row['name']); ?>" required><br><br>
                <label for="dob">Date of Birth:</label>
                <input type="date" name="dob" id="dob" value="<?php echo htmlspecialchars($row['dob']); ?>" ><br><br>
                <label for="pic">Profile Picture:</label>
                <input type="file" name="pic" id="pic"><br><br>
                <label for="address">Address:</label>
                <textarea name="address" id="address" required><?php echo htmlspecialchars($row['address']); ?></textarea><br><br>
                <label for="mobile">Mobile Number:</label>
                <input type="tel" name="mobile" id="mobile" value="<?php echo htmlspecialchars($row['mobile']); ?>" required><br><br>
                <input type="hidden" name="serial" value="<?php echo $row['serial']; ?>">
                <input type="submit" name="edit" value="Update">
            </form>
        </div>
    <?php } ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
    var buttonNew = document.getElementById("new");
    var overlay = document.getElementById("overlay");
    var popup = document.getElementById("popup");
    var delButton = document.getElementById("del");

    buttonNew.addEventListener("click", function() {
        overlay.style.display = "block";
        popup.style.display = "block";
    });

    overlay.addEventListener("click", function() {
        overlay.style.display = "none";
        popup.style.display = "none";
    });

    var editButtons = document.querySelectorAll(".editClick");
    var overlay2 = document.getElementById("overlay2");
    var popups = document.querySelectorAll(".popup2");

    editButtons.forEach(function(button) {
        button.addEventListener("click", function(event) {
            event.preventDefault();
            var form = button.closest(".form3");
            var serial = form.querySelector("input[name='serial']").value;
            var editPopup = document.getElementById("popup_edit_" + serial);
            overlay2.style.display = "block";
            editPopup.style.display = "block";
        });
    });

    overlay2.addEventListener("click", function(event) {
        if (event.target === overlay2) {
            overlay2.style.display = "none";
            popups.forEach(function(popup) {
                popup.style.display = "none";
            });
        }
    });

    delButton.addEventListener("click", function (event) {
        event.preventDefault();
        var form = document.querySelector(".form3");
        var checkboxes = form.querySelectorAll("input[type='checkbox']:checked");
        if (checkboxes.length > 0) {
            console.log("Form will be submitted.");
            form.submit();
        } else {
            alert("Please select at least one user to delete.");
        }
    });
});

function sortTable(columnIndex) {
    var table, rows, switching, i, x, y, shouldSwitch, switchcount = 0;
    table = document.getElementById("studentTable");
    switching = true;
    var dir = table.getAttribute("data-sort-dir-" + columnIndex) || "asc"; // Get the current sorting direction

    while (switching) {
        switching = false;
        rows = table.rows;

        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].querySelectorAll("td")[columnIndex];
            y = rows[i + 1].querySelectorAll("td")[columnIndex];

            if (dir === "asc") {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir === "desc") {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            }
        }

        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount++;
        } else {
            if (switchcount === 0) {
                if (dir === "asc") {
                    dir = "desc";
                    switching = true;
                } else if (dir === "desc") {
                    dir = "asc";
                    switching = true;
                }
            }
        }
    }

    table.setAttribute("data-sort-dir-" + columnIndex, dir); // Save the current sorting direction
}

document.addEventListener("DOMContentLoaded", function () {
    var headers = document.querySelectorAll(".sortable");

    headers.forEach(function (header, index) {
        header.addEventListener("click", function () {
            sortTable(index);
        });
    });
});
    </script>
</body>
</html>

<?php
if (isset($_POST['submit'])) { //ADD STUDENT
    $server = "localhost";
    $username = "root";
    $password = "";
    $dbname = "students";
    $conn = mysqli_connect($server,$username,$password,$dbname);
    if(!$conn) die("connection to this database failed due to" . mysqli_connect_error());

    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $mobile = $_POST['mobile'];

    if (isset($_FILES["pic"]) && $_FILES["pic"]["error"] === UPLOAD_ERR_OK) {
        $pic = file_get_contents($_FILES["pic"]["tmp_name"]);
        $pic = $conn->real_escape_string($pic);
    }

    $sql = "INSERT INTO `students`.`students` (`name`, `dob`, `pic`, `address`, `mobile`, `last_modifed`) 
    VALUES ('$name', '$dob', '$pic', '$address', '$mobile', current_timestamp())";

    if($conn->query($sql)==true){
        echo '<script>window.location.href = window.location.pathname;</script>';
        exit;
    }
    else echo "Error: $sql <br> $con->error";
        
    $conn->close();
}   

if (isset($_POST['delete'])){ //DELETE SINGLE STUDENT
    $server = "localhost";
    $username = "root";
    $password = "";
    $dbname = "students";
    $conn = mysqli_connect($server,$username,$password,$dbname);
    if(!$conn) die("connection to this database failed due to" . mysqli_connect_error());

    $serial = $_POST['serial'];

    $sql = "DELETE FROM students WHERE `students`.`serial` = $serial";

    if($conn->query($sql)==true){
        echo '<script>window.location.href = window.location.pathname;</script>';
        exit;
    }
    else echo "Error: $sql <br> $con->error";
        
    $conn->close();
}

if (isset($_POST['edit'])) { //EDIT STUDENT
    $server = "localhost";
    $username = "root";
    $password = "";
    $dbname = "students";
    $conn = mysqli_connect($server, $username, $password, $dbname);
    if (!$conn) die("connection to this database failed due to" . mysqli_connect_error());

    $serial = $_POST['serial'];
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $mobile = $_POST['mobile'];

    if (isset($_FILES["pic"]) && $_FILES["pic"]["error"] === UPLOAD_ERR_OK) {
        $pic = file_get_contents($_FILES["pic"]["tmp_name"]);
        $pic = $conn->real_escape_string($pic);
        $updatePic = ", `pic` = '$pic'";
    } else {
        $updatePic = "";
    }

    $sql = "UPDATE `students` SET `name` = '$name', `dob` = '$dob', `address` = '$address', `mobile` = '$mobile', `last_modifed` = current_timestamp() $updatePic WHERE `serial` = $serial";

    if ($conn->query($sql) === true) {
        echo '<script>window.location.href = window.location.pathname;</script>';
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $conn->close();
}

if (isset($_POST['deleteSelected'])) { //DELETE MULTIPLE STUDENTS
    $server = "localhost";
    $username = "root";
    $password = "";
    $dbname = "students";
    $conn = mysqli_connect($server, $username, $password, $dbname);
    if (!$conn) die("connection to this database failed due to" . mysqli_connect_error());

    if (isset($_POST['selectedStudents']) && is_array($_POST['selectedStudents'])) {
        $selectedStudents = $_POST['selectedStudents'];
        $selectedStudents = array_map('intval', $selectedStudents); // Ensure integers for security

        if (!empty($selectedStudents)) {
            $selectedStudentsStr = implode(",", $selectedStudents);

            $sql = "DELETE FROM students WHERE `students`.`serial` IN ($selectedStudentsStr)";

            if ($conn->query($sql) === true) {
                echo '<script>window.location.href = window.location.pathname;</script>';
                exit;
            } else {
                echo "Error deleting records: " . $conn->error;
            }
        }
    }

    $conn->close();
}
?>