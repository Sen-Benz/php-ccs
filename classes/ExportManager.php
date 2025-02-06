<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExportManager {
    private $db;
    private $spreadsheet;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->spreadsheet = new Spreadsheet();
    }

    public function exportScreeningResults($filters = []) {
        try {
            // Create first sheet for Exam Results
            $this->spreadsheet->setActiveSheetIndex(0);
            $examSheet = $this->spreadsheet->getActiveSheet();
            $examSheet->setTitle('Exam Results');

            // Set headers for Exam Results sheet
            $examSheet->setCellValue('A1', 'Name');
            $examSheet->setCellValue('B1', 'Part 1 Score');
            $examSheet->setCellValue('C1', 'Part 2 Score');
            $examSheet->setCellValue('D1', 'Interview Score');
            $examSheet->setCellValue('E1', 'Remarks');

            // Style the header row
            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'CCCCCC']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ]
            ];
            $examSheet->getStyle('A1:E1')->applyFromArray($headerStyle);

            // Get exam results data
            $whereClause = $this->buildWhereClause($filters);
            $query = "SELECT 
                        CONCAT(a.first_name, ' ', a.last_name) as name,
                        er1.score as part1_score,
                        er2.score as part2_score,
                        i.score as interview_score,
                        es.part1_status,
                        es.part2_status,
                        es.interview_status
                     FROM applicants a
                     LEFT JOIN exam_results er1 ON a.id = er1.applicant_id AND er1.exam_id = 1
                     LEFT JOIN exam_results er2 ON a.id = er2.applicant_id AND er2.exam_id = 2
                     LEFT JOIN interviews i ON a.id = i.applicant_id
                     LEFT JOIN exam_status es ON a.id = es.applicant_id
                     WHERE 1=1 " . $whereClause;

            $stmt = $this->db->query($query);
            $results = $stmt->fetchAll();

            $row = 2;
            foreach ($results as $result) {
                // Apply business rules for scores display
                $part1Score = $result['part1_score'] ?? '-';
                $part2Score = ($part1Score >= 75) ? ($result['part2_score'] ?? '-') : '-';
                $interviewScore = ($part1Score >= 75 && $part2Score >= 75) ? ($result['interview_score'] ?? '-') : '-';
                
                // Determine remarks
                $remarks = 'Fail';
                if ($part1Score >= 75 && $part2Score >= 75 && $interviewScore >= 75) {
                    $remarks = 'Pass';
                }

                $examSheet->setCellValue('A' . $row, $result['name']);
                $examSheet->setCellValue('B' . $row, $part1Score);
                $examSheet->setCellValue('C' . $row, $part2Score);
                $examSheet->setCellValue('D' . $row, $interviewScore);
                $examSheet->setCellValue('E' . $row, $remarks);

                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'E') as $col) {
                $examSheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Create second sheet for Status
            $statusSheet = $this->spreadsheet->createSheet();
            $statusSheet->setTitle('Applicant & Exam Status');

            // Set headers for Status sheet
            $statusSheet->setCellValue('A1', 'Name');
            $statusSheet->setCellValue('B1', 'Applicant Status');
            $statusSheet->setCellValue('C1', 'Part 1 Status');
            $statusSheet->setCellValue('D1', 'Part 2 Status');
            $statusSheet->setCellValue('E1', 'Interview Status');

            // Style the header row
            $statusSheet->getStyle('A1:E1')->applyFromArray($headerStyle);

            // Get status data
            $query = "SELECT 
                        CONCAT(a.first_name, ' ', a.last_name) as name,
                        aps.status as applicant_status,
                        es.part1_status,
                        es.part2_status,
                        es.interview_status
                     FROM applicants a
                     LEFT JOIN applicant_status aps ON a.id = aps.applicant_id
                     LEFT JOIN exam_status es ON a.id = es.applicant_id
                     WHERE 1=1 " . $whereClause;

            $stmt = $this->db->query($query);
            $statuses = $stmt->fetchAll();

            $row = 2;
            foreach ($statuses as $status) {
                $part1Status = $status['part1_status'];
                $part2Status = ($part1Status === 'Completed') ? $status['part2_status'] : 'Not Started';
                $interviewStatus = ($part2Status === 'Completed') ? $status['interview_status'] : 'Not Started';

                // If applicant is rejected, leave exam statuses blank
                if ($status['applicant_status'] === 'Rejected') {
                    $part1Status = '';
                    $part2Status = '';
                    $interviewStatus = '';
                }

                $statusSheet->setCellValue('A' . $row, $status['name']);
                $statusSheet->setCellValue('B' . $row, $status['applicant_status']);
                $statusSheet->setCellValue('C' . $row, $part1Status);
                $statusSheet->setCellValue('D' . $row, $part2Status);
                $statusSheet->setCellValue('E' . $row, $interviewStatus);

                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'E') as $col) {
                $statusSheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Set first sheet as active
            $this->spreadsheet->setActiveSheetIndex(0);

            // Generate filename with timestamp
            $timestamp = date('Y-m-d_His');
            $filename = "Screening_Results_{$timestamp}.xlsx";
            $filepath = __DIR__ . "/../exports/" . $filename;

            // Create exports directory if it doesn't exist
            if (!file_exists(__DIR__ . "/../exports")) {
                mkdir(__DIR__ . "/../exports", 0777, true);
            }

            // Save the file
            $writer = new Xlsx($this->spreadsheet);
            $writer->save($filepath);

            // Log the export
            $this->logExport($filename, 'XLSX', $filters);

            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath
            ];

        } catch (Exception $e) {
            error_log("Export error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function buildWhereClause($filters) {
        $where = [];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where[] = "a.created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "a.created_at <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['status'])) {
            $where[] = "aps.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['applicant_id'])) {
            $where[] = "a.id = ?";
            $params[] = $filters['applicant_id'];
        }

        return !empty($where) ? " AND " . implode(" AND ", $where) : "";
    }

    private function logExport($filename, $type, $filters) {
        $query = "INSERT INTO export_history (admin_id, filename, export_type, filters) VALUES (?, ?, ?, ?)";
        $this->db->query($query, [
            $_SESSION['user_id'],
            $filename,
            $type,
            json_encode($filters)
        ]);
    }
}
