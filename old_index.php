<?php
// Database configuration
$server = "localhost";
$username = "root";
$password = "";
$dbname = "students";

// Function to connect to the database
function connectToDatabase()
{
    global $server, $username, $password, $dbname;
    $conn = mysqli_connect($server, $username, $password, $dbname);
    if (!$conn) {
        die("Connection to the database failed due to: " . mysqli_connect_error());
    }
    return $conn;
}

// Function to execute a query
function executeQuery($conn, $sql)
{
    if ($conn->query($sql) === true) {
        return true;
    } else {
        echo "Error executing query: " . $conn->error;
        return false;
    }
}

// Function to get students data with sorting
function getStudentsData($sortColumn, $sortDirection)
{
    $conn = connectToDatabase();
    $allowedColumns = array('serial', 'name', 'dob', 'address', 'mobile');
    if (!in_array($sortColumn, $allowedColumns)) {
        $sortColumn = 'serial';
    }
    $sql = "SELECT * FROM students ORDER BY $sortColumn $sortDirection";
    $result = $conn->query($sql);
    $data = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $conn->close();
    return $data;
}

// Function to add a new student
function addStudent()
{
    if (isset($_POST['submit'])) {
        $conn = connectToDatabase();
        $name = $_POST['name'];
        $dob = $_POST['dob'];
        $address = $_POST['address'];
        $mobile = $_POST['mobile'];

        $pic = "";
        if (isset($_FILES["pic"]) && $_FILES["pic"]["error"] === UPLOAD_ERR_OK) {
            $pic = file_get_contents($_FILES["pic"]["tmp_name"]);
            $pic = $conn->real_escape_string($pic);
        }

        $sql = "INSERT INTO `students` (`name`, `dob`, `pic`, `address`, `mobile`, `last_modifed`) 
                VALUES ('$name', '$dob', '$pic', '$address', '$mobile', current_timestamp())";

        executeQuery($conn, $sql);
    }
}

// Function to delete a student
function deleteStudent($serial)
{
    $conn = connectToDatabase();
    $sql = "DELETE FROM students WHERE `students`.`serial` = $serial";
    executeQuery($conn, $sql);
}

// Function to edit a student
function editStudent($serial, $name, $dob, $address, $mobile, $pic)
{
    $conn = connectToDatabase();
    if (isset($pic) && $pic["error"] === UPLOAD_ERR_OK) {
        $picData = file_get_contents($pic["tmp_name"]);
        $picData = $conn->real_escape_string($picData);
        $updatePic = ", `pic` = '$picData'";
    } else {
        $updatePic = "";
    }

    $sql = "UPDATE `students` SET `name` = '$name', `dob` = '$dob', `address` = '$address', `mobile` = '$mobile', `last_modifed` = current_timestamp() $updatePic WHERE `serial` = $serial";

    executeQuery($conn, $sql);
}

// Function to delete multiple students
function deleteSelectedStudents($selectedStudents)
{
    if (is_array($selectedStudents) && !empty($selectedStudents)) {
        $conn = connectToDatabase();
        $selectedStudentsStr = implode(",", array_map('intval', $selectedStudents)); // Ensure integers for security
        $sql = "DELETE FROM students WHERE `students`.`serial` IN ($selectedStudentsStr)";
        executeQuery($conn, $sql);
    }
}

// Call the necessary functions
addStudent();

if (isset($_POST['delete'])) {
    $serialToDelete = $_POST['serial'];
    deleteStudent($serialToDelete);
}

if (isset($_POST['edit'])) {
    $serialToEdit = $_POST['serial'];
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $mobile = $_POST['mobile'];
    $pic = $_FILES["pic"];
    editStudent($serialToEdit, $name, $dob, $address, $mobile, $pic);
}

if (isset($_POST['deleteSelected'])) {
    $selectedStudents = $_POST['selectedStudents'];
    deleteSelectedStudents($selectedStudents);
}

// Get sorting parameters from URL
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'serial';
$sortDirection = isset($_GET['dir']) && strtolower($_GET['dir']) === 'desc' ? 'DESC' : 'ASC';

// Get data based on sorting parameters
$data = getStudentsData($sortColumn, $sortDirection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>User Data</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Manage Users</h1>
        <div class="button-container">
            <button id="new" class="button1"><b>Add New User</b></button>
            <input type="submit" id="del" name="deleteSelected" class="button2" value="Delete Users">
        </div>
    </div>

    <div class="table-container">
        <h1>Students</h1>
        <?php if (isset($data) && count($data) > 0) { ?>
            <form class="form3" id="form3" method="POST">
            <table id="studentTable">
                <tr>
                    <th></th>
                    <th class="sortable" data-column="SR"><a href="?sort=serial"> SR</a></th>
                    <th class="sortable" data-column="Name"><a href="?sort=name"> Name</a></th>
                    <th class="sortable" data-column="Birth"><a href="?sort=dob"> Birth</a></th>
                    <th class="sortable" data-column="Address"><a href="?sort=address"> Address</a></th>
                    <th class="sortable" data-column="Number"><a href="?sort=mobile"> Number</a></th>
                    <th>Action</th>
                </tr>
                <?php foreach ($data as $index => $row) { ?>
                    <tr>
                        <td><input type="checkbox" name="selectedStudents[]" value="<?php echo $row['serial']; ?>"></td>
                        <td><?php echo ++$index; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['dob']; ?></td>
                        <td><?php echo $row['address']; ?></td>
                        <td><?php echo $row['mobile']; ?></td>
                        <td>
                            <form class="form3" method="POST">
                                <input type="hidden" name="serial" value="<?php echo $row['serial']; ?>">
                                <input type="submit" class="button3 editClick" name="edit" value="">
                                <input type="submit" class="button4" name="delete" value="">
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
            <input type="date" name="dob" id="dob"><br><br>
            <label for="pic">Profile Picture:</label>
            <input type="file" name="pic" id="pic"><br><br>
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
                <input type="date" name="dob" id="dob" value="<?php echo htmlspecialchars($row['dob']); ?>"><br><br>
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
document.addEventListener("DOMContentLoaded", function () {
  var buttonNew = document.getElementById("new");
  var overlay = document.getElementById("overlay");
  var popup = document.getElementById("popup");
  var delButton = document.getElementById("del");

  buttonNew.addEventListener("click", function () {
    overlay.style.display = "block";
    popup.style.display = "block";
  });

  overlay.addEventListener("click", function (event) {
    if (event.target === overlay) {
      overlay.style.display = "none";
      popup.style.display = "none";
    }
  });

  var editButtons = document.querySelectorAll(".editClick");
  var overlay2 = document.getElementById("overlay2");
  var popups = document.querySelectorAll(".popup2");

  editButtons.forEach(function (button) {
    button.addEventListener("click", function (event) {
      event.preventDefault();
      var form = button.closest(".form3");
      var serial = form.querySelector("input[name='serial']").value;
      var editPopup = document.getElementById("popup_edit_" + serial);
      overlay2.style.display = "block";
      editPopup.style.display = "block";
    });
  });

  overlay2.addEventListener("click", function (event) {
    if (event.target === overlay2) {
      overlay2.style.display = "none";
      popups.forEach(function (popup) {
        popup.style.display = "none";
      });
    }
  });

  delButton.addEventListener("click", function (event) {
    event.preventDefault();
    var form = document.querySelector(".form3");
    var checkboxes = form.querySelectorAll("input[type='checkbox']:checked");
    if (checkboxes.length > 0) {
      form.submit();
    } else {
      alert("Please select at least one user to delete.");
    }
  });
});

function sortTable(columnIndex) {
  var table = document.getElementById("studentTable");
  var rows = table.rows;
  var switching = true;
  var dir = table.getAttribute("data-sort-dir-" + columnIndex) || "asc"; // Get the current sorting direction

  while (switching) {
    switching = false;
    for (var i = 1; i < rows.length - 1; i++) {
      var shouldSwitch = false;
      var x = rows[i].querySelectorAll("td")[columnIndex];
      var y = rows[i + 1].querySelectorAll("td")[columnIndex];

      if (dir === "asc" ? x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase() : x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
        shouldSwitch = true;
        break;
      }
    }

    if (shouldSwitch) {
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      switchcount++;
    } else {
      if (switchcount === 0) {
        dir = dir === "asc" ? "desc" : "asc";
        switching = true;
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
