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