<?php
// Include your existing database connection
require_once 'db_connection.php';

// Include TCPDF library
require_once 'tcpdf/tcpdf.php';

// Check if the staff_id is provided via GET request
if (!isset($_GET['staff_id'])) {
    echo "Staff ID not provided.";
    exit();
}

// Retrieve staff_id from the GET request
$staffId = $_GET['staff_id'];

// Prepare and execute the query to fetch timetable details
$getTimetableQuery = "SELECT * FROM staff_timetable WHERE staff_id = ?";
$stmt = $conn->prepare($getTimetableQuery);
$stmt->bind_param("i", $staffId);
$stmt->execute();
$result = $stmt->get_result();

// Check if any timetable is found
if ($result->num_rows > 0) {
    // Initialize TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Name');
    $pdf->SetTitle('Staff Timetable');
    $pdf->SetSubject('Staff Timetable');
    $pdf->SetKeywords('Staff Timetable, PDF');

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // Add content
    $content = '<h1>Staff Timetable</h1>';
    $content .= '<table border="1">';
    $content .= '<thead><tr><th>Session/Day</th>';
    for ($day = 1; $day <= 6; $day++) {
        $content .= '<th>Day ' . $day . '</th>';
    }
    $content .= '</tr></thead><tbody>';
    for ($session = 1; $session <= 6; $session++) {
        $content .= '<tr><td>Session ' . $session . '</td>';
        for ($day = 1; $day <= 6; $day++) {
            $subjectName = '';
            while ($row = $result->fetch_assoc()) {
                if ($row['day'] == $day && $row['session'] == $session) {
                    $subjectName = getSubjectName($conn, $row['subject_id']);
                    break;
                }
            }
            $content .= '<td>' . $subjectName . '</td>';
            $result->data_seek(0);
        }
        $content .= '</tr>';
    }
    $content .= '</tbody></table>';

    // Write the HTML content to the PDF
    $pdf->writeHTML($content, true, false, true, false, '');

    // Close and output PDF
    $pdf->Output('staff_timetable.pdf', 'D');
} else {
    echo "No timetable found.";
}

// Close the prepared statement
$stmt->close();

// Function to get subject name by subject ID
function getSubjectName($conn, $subjectId) {
    $query = "SELECT subject_name FROM subjects WHERE subject_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $subjectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $subjectName = '';
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $subjectName = $row['subject_name'];
    }
    $stmt->close();
    return $subjectName;
}
?>
