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

$category = $_GET["cat"] ?? null;
$view = $_GET["view"] ?? null;
$query = "SELECT * from PRESCRIPTION";
$stmt = $db->stmt_init();
$stmt->prepare($query);
$stmt->execute();
$stmt->bind_result($prescriptionid, $dateprescribed, $validperiod, $patientid, $doctorid);

$prescriptions = [];
while ($stmt->fetch()) {
    $prescription = new Prescription($prescriptionid, $dateprescribed, $validperiod, $patientid, $doctorid);
    $prescriptions[] = $prescription;
}

echo json_encode($prescriptions);
$stmt->close();
?>