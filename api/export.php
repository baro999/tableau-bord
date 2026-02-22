<?php
// api/export.php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier la connexion
if (!estConnecte()) {
    header('Location: ../login.php');
    exit();
}

$conn = getDB();
$format = $_GET['format'] ?? 'csv';
$module = $_GET['module'] ?? 'all';

// Définir le nom du fichier
$filename = 'export_' . $module . '_' . date('Y-m-d_H-i-s');

// Fonction pour exporter en CSV
function exportCSV($data, $filename) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM pour UTF-8
    
    if (!empty($data)) {
        // Écrire l'en-tête
        fputcsv($output, array_keys($data[0]), ';');
        
        // Écrire les données
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }
    }
    
    fclose($output);
}

// Récupérer les données selon le module
switch ($module) {
    case 'indicateurs':
        $query = "SELECT * FROM indicateurs ORDER BY direction, nom";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll();
        exportCSV($data, $filename);
        break;
        
    case 'problemes':
        $query = "SELECT * FROM problemes ORDER BY date_creation DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll();
        exportCSV($data, $filename);
        break;
        
    case 'projets':
        $query = "SELECT * FROM projets ORDER BY date_creation DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll();
        exportCSV($data, $filename);
        break;
        
    case 'pta':
        $query = "SELECT * FROM pta_activites ORDER BY date_debut DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll();
        exportCSV($data, $filename);
        break;
        
    case 'points':
        $query = "SELECT * FROM points_focaux ORDER BY direction";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll();
        exportCSV($data, $filename);
        break;
        
    case 'all':
    default:
        // Export complet avec plusieurs onglets pour Excel
        if ($format === 'excel') {
            // Pour Excel, on pourrait générer un vrai fichier XLSX
            // Mais pour l'instant, on exporte tout en CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '_complet.csv"');
            
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM pour UTF-8
            
            // Indicateurs
            fputcsv($output, ['=== INDICATEURS ==='], ';');
            $query = "SELECT * FROM indicateurs";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll();
            if (!empty($data)) {
                fputcsv($output, array_keys($data[0]), ';');
                foreach ($data as $row) {
                    fputcsv($output, $row, ';');
                }
            }
            fputcsv($output, [], ';');
            
            // Problèmes
            fputcsv($output, ['=== PROBLEMES ==='], ';');
            $query = "SELECT * FROM problemes";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll();
            if (!empty($data)) {
                fputcsv($output, array_keys($data[0]), ';');
                foreach ($data as $row) {
                    fputcsv($output, $row, ';');
                }
            }
            fputcsv($output, [], ';');
            
            // Projets
            fputcsv($output, ['=== PROJETS ==='], ';');
            $query = "SELECT * FROM projets";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll();
            if (!empty($data)) {
                fputcsv($output, array_keys($data[0]), ';');
                foreach ($data as $row) {
                    fputcsv($output, $row, ';');
                }
            }
            fputcsv($output, [], ';');
            
            // PTA
            fputcsv($output, ['=== ACTIVITES PTA ==='], ';');
            $query = "SELECT * FROM pta_activites";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll();
            if (!empty($data)) {
                fputcsv($output, array_keys($data[0]), ';');
                foreach ($data as $row) {
                    fputcsv($output, $row, ';');
                }
            }
            fputcsv($output, [], ';');
            
            // Points focaux
            fputcsv($output, ['=== POINTS FOCAUX ==='], ';');
            $query = "SELECT * FROM points_focaux";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll();
            if (!empty($data)) {
                fputcsv($output, array_keys($data[0]), ';');
                foreach ($data as $row) {
                    fputcsv($output, $row, ';');
                }
            }
            
            fclose($output);
        } else {
            // Export CSV simple
            $query = "SELECT 'INDICATEURS' as module, COUNT(*) as total FROM indicateurs
                      UNION ALL SELECT 'PROBLEMES', COUNT(*) FROM problemes
                      UNION ALL SELECT 'PROJETS', COUNT(*) FROM projets
                      UNION ALL SELECT 'PTA', COUNT(*) FROM pta_activites
                      UNION ALL SELECT 'POINTS FOCAUX', COUNT(*) FROM points_focaux";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll();
            exportCSV($data, $filename . '_stats');
        }
        break;
}

// Journaliser l'export
ajouterHistorique('Export', $format, "Export $module au format $format", $conn);
exit();
?>