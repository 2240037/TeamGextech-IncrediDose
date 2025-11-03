<?php
include("db.php");

class Prescription implements JsonSerializable {
    private $prescriptionid;
    private $dateprescribed;
    private $validperiod;
    private $patientid;
    private $doctorid;

    public function __construct($prescriptionid, $dateprescribed, $validperiod, $patientid, $doctorid) {
        $this->prescriptionid = $prescriptionid;
        $this->dateprescribed = $dateprescribed;
        $this->validperiod = $validperiod;
        $this->patientid = $patientid;
        $this->doctorid = $doctorid;
    }

    public function get_prescriptionid() {
        return $this->prescriptionid;
    }

    public function get_dateprescribed() {
        return $this->dateprescribed;
    }
    
    public function get_validperiod() {
        return $this->validperiod;
    }

    public function get_patientid() {
        return $this->patientid;
    }

    public function get_doctorid() {
        return $this->doctorid;
    }

    public function jsonSerialize(): array {
        return [
            'prescriptionid' => $this->prescriptionid,
            'dateprescribed' => $this->dateprescribed,
            'validperiod' => $this->validperiod,
            'patientid' => $this->patientid,
            'doctorid' => $this->doctorid
        ];
    }
}


function addPrescription($dateprescribed, $validperiod, $patientid, $doctorid) {
    global $db;
    
    try {
        $query = "INSERT INTO PRESCRIPTION (dateprescribed, validperiod, patientid, doctorid) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ssii", $dateprescribed, $validperiod, $patientid, $doctorid);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Prescription added successfully',
                'prescription_id' => $stmt->insert_id
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to add prescription: ' . $stmt->error
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $dateprescribed = $input['dateprescribed'] ?? '';
    $validperiod = $input['validperiod'] ?? '';
    $patientid = $input['patientid'] ?? '';
    $doctorid = $input['doctorid'] ?? '';
    
    $result = addPrescription($dateprescribed, $validperiod, $patientid, $doctorid);
    echo json_encode($result);

} else {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    $search = isset($_GET['search']) ? trim($_GET['search']) : null;
    $patientid = isset($_GET['patientid']) && $_GET['patientid'] !== '' ? (int)$_GET['patientid'] : null;
    $doctorid  = isset($_GET['doctorid'])  && $_GET['doctorid'] !== ''  ? (int)$_GET['doctorid']  : null;
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : null;
    $date_to   = isset($_GET['date_to'])   ? $_GET['date_to']   : null;

    // Sorting 
    $allowed_sort = ['prescriptionid','dateprescribed','validperiod','patientid','doctorid'];
    $sort_by = isset($_GET['sort_by']) && in_array($_GET['sort_by'], $allowed_sort) ? $_GET['sort_by'] : 'dateprescribed';
    $sort_dir = (isset($_GET['sort_dir']) && strtolower($_GET['sort_dir']) === 'asc') ? 'ASC' : 'DESC';

    $conditions = [];
    $params = [];     // values
    $types = '';      // mysqli bind types

    if ($search !== null && $search !== '') {
        $conditions[] = "(CAST(prescriptionid AS CHAR) LIKE ? OR dateprescribed LIKE ?)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
        $types .= 'ss';
    }

    if ($patientid !== null) {
        $conditions[] = "patientid = ?";
        $params[] = $patientid;
        $types .= 'i';
    }

    if ($doctorid !== null) {
        $conditions[] = "doctorid = ?";
        $params[] = $doctorid;
        $types .= 'i';
    }

    if ($date_from !== null && $date_to !== null) {
        $conditions[] = "dateprescribed BETWEEN ? AND ?";
        $params[] = $date_from;
        $params[] = $date_to;
        $types .= 'ss';
    } else if ($date_from !== null) {
        $conditions[] = "dateprescribed >= ?";
        $params[] = $date_from;
        $types .= 's';
    } else if ($date_to !== null) {
        $conditions[] = "dateprescribed <= ?";
        $params[] = $date_to;
        $types .= 's';
    }

    $where = '';
    if (!empty($conditions)) {
        $where = ' WHERE ' . implode(' AND ', $conditions);
    }

    // Final SELECT with ORDER BY 
    $sql = "SELECT prescriptionid, dateprescribed, validperiod, patientid, doctorid
            FROM PRESCRIPTION
            $where
            ORDER BY $sort_by $sort_dir";

    $stmt = $db->prepare($sql);

    
    if ($types !== '') {
        $bind_names = [];
        $bind_names[] = &$types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_names[] = &$params[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
    }

    // Fetch
    $stmt->execute();
    $stmt->bind_result($prescriptionid, $dateprescribed, $validperiod, $patientidRes, $doctoridRes);

    $prescriptions = [];
    while ($stmt->fetch()) {
        $prescriptions[] = [
            'prescriptionid' => $prescriptionid,
            'dateprescribed' => $dateprescribed,
            'validperiod' => $validperiod,
            'patientid' => $patientidRes,
            'doctorid' => $doctoridRes
        ];
    }
    $stmt->close();

   
    header('Content-Type: application/json');
    echo json_encode([
        'data' => $prescriptions,
        'count' => count($prescriptions)
    ]);
    exit;
}


}
// sample localhost queries for testing
// http://localhost/TeamGextech-IncrediDose/includes/prescription.php
// http://localhost/TeamGextech-IncrediDose/includes/prescription.php?userid=21
/* Get all	- prescription.php
Search	- prescription.php?search=2025
Filter by patient	- prescription.php?patientid=4
Filter by doctor - 	prescription.php?doctorid=2
Date range -	prescription.php?date_from=2025-01-01&date_to=2025-01-31
Sort by valid period (ascending)	- prescription.php?sort_by=validperiod&sort_dir=asc
Combined - prescription.php?doctorid=2&sort_by=dateprescribed&sort_dir=desc 
*/

?>