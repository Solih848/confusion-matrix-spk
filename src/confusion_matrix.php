<?php

/**
 * Kelas ConfusionMatrix untuk menghitung metrik confusion matrix
 */
class ConfusionMatrix
{
    private $confusionMatrix = [];
    private $classes = [];
    private $data = [];

    /**
     * Menghitung confusion matrix dari data yang diberikan
     * 
     * @param array $data Array data dalam format [['actual' => 'class1', 'predicted' => 'class2'], ...]
     * @param array $classes Array kelas unik
     */
    public function calculateConfusionMatrix($data, $classes)
    {
        $this->data = $data;
        $this->classes = $classes;

        // Inisialisasi confusion matrix dengan nilai 0
        foreach ($classes as $actualClass) {
            $this->confusionMatrix[$actualClass] = [];
            foreach ($classes as $predictedClass) {
                $this->confusionMatrix[$actualClass][$predictedClass] = 0;
            }
        }

        // Hitung frekuensi untuk setiap kombinasi actual dan predicted
        foreach ($data as $item) {
            $actual = $item['actual'];
            $predicted = $item['predicted'];

            if (isset($this->confusionMatrix[$actual][$predicted])) {
                $this->confusionMatrix[$actual][$predicted]++;
            }
        }
    }

    /**
     * Mendapatkan confusion matrix
     * 
     * @return array Confusion matrix
     */
    public function getConfusionMatrix()
    {
        return $this->confusionMatrix;
    }

    /**
     * Mendapatkan metrik untuk kelas tertentu
     * 
     * @param string $class Nama kelas
     * @return array Metrik confusion matrix (TP, FP, TN, FN, precision, recall, f1_score)
     */
    public function getMetrics($class)
    {
        // Hitung True Positive (TP)
        $tp = isset($this->confusionMatrix[$class][$class]) ? $this->confusionMatrix[$class][$class] : 0;

        // Hitung False Positive (FP)
        $fp = 0;
        foreach ($this->classes as $actualClass) {
            if ($actualClass !== $class && isset($this->confusionMatrix[$actualClass][$class])) {
                $fp += $this->confusionMatrix[$actualClass][$class];
            }
        }

        // Hitung False Negative (FN)
        $fn = 0;
        foreach ($this->classes as $predictedClass) {
            if ($predictedClass !== $class && isset($this->confusionMatrix[$class][$predictedClass])) {
                $fn += $this->confusionMatrix[$class][$predictedClass];
            }
        }

        // Hitung True Negative (TN)
        $tn = 0;
        foreach ($this->classes as $actualClass) {
            foreach ($this->classes as $predictedClass) {
                if ($actualClass !== $class && $predictedClass !== $class) {
                    $tn += isset($this->confusionMatrix[$actualClass][$predictedClass]) ?
                        $this->confusionMatrix[$actualClass][$predictedClass] : 0;
                }
            }
        }

        // Hitung precision, recall, dan f1-score
        $precision = ($tp + $fp > 0) ? $tp / ($tp + $fp) : 0;
        $recall = ($tp + $fn > 0) ? $tp / ($tp + $fn) : 0;
        $f1Score = ($precision + $recall > 0) ? 2 * ($precision * $recall) / ($precision + $recall) : 0;

        return [
            'tp' => $tp,
            'fp' => $fp,
            'tn' => $tn,
            'fn' => $fn,
            'precision' => $precision,
            'recall' => $recall,
            'f1_score' => $f1Score
        ];
    }

    /**
     * Mendapatkan akurasi keseluruhan
     * 
     * @return float Akurasi
     */
    public function getAccuracy()
    {
        $totalCorrect = 0;
        $totalInstances = count($this->data);

        foreach ($this->classes as $class) {
            $totalCorrect += isset($this->confusionMatrix[$class][$class]) ? $this->confusionMatrix[$class][$class] : 0;
        }

        return ($totalInstances > 0) ? $totalCorrect / $totalInstances : 0;
    }

    /**
     * Mendapatkan matriks dalam format HTML untuk ditampilkan
     * 
     * @return string HTML tabel confusion matrix
     */
    public function getHtmlMatrix()
    {
        $html = '<div class="confusion-matrix-container">';
        $html .= '<h3>Confusion Matrix</h3>';
        $html .= '<table class="confusion-matrix">';

        // Header
        $html .= '<tr><th></th><th colspan="' . count($this->classes) . '">Predicted</th></tr>';
        $html .= '<tr><th>Actual</th>';
        foreach ($this->classes as $class) {
            $html .= '<th>' . htmlspecialchars($class) . '</th>';
        }
        $html .= '</tr>';

        // Body
        foreach ($this->classes as $actualClass) {
            $html .= '<tr>';
            $html .= '<th>' . htmlspecialchars($actualClass) . '</th>';

            foreach ($this->classes as $predictedClass) {
                $value = isset($this->confusionMatrix[$actualClass][$predictedClass]) ?
                    $this->confusionMatrix[$actualClass][$predictedClass] : 0;

                $class = '';
                if ($actualClass === $predictedClass) {
                    $class = 'correct';
                } else {
                    $class = 'incorrect';
                }

                $html .= '<td class="' . $class . '">' . $value . '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Mendapatkan metrik dalam format HTML untuk ditampilkan
     * 
     * @return string HTML tabel metrik
     */
    public function getHtmlMetrics()
    {
        $html = '<div class="metrics-container">';
        $html .= '<h3>Metrik per Kelas</h3>';
        $html .= '<table class="metrics-table">';

        // Header
        $html .= '<tr><th>Kelas</th><th>TP</th><th>FP</th><th>TN</th><th>FN</th><th>Precision</th><th>Recall</th><th>F1-Score</th></tr>';

        // Body
        foreach ($this->classes as $class) {
            $metrics = $this->getMetrics($class);

            $html .= '<tr>';
            $html .= '<th>' . htmlspecialchars($class) . '</th>';
            $html .= '<td>' . $metrics['tp'] . '</td>';
            $html .= '<td>' . $metrics['fp'] . '</td>';
            $html .= '<td>' . $metrics['tn'] . '</td>';
            $html .= '<td>' . $metrics['fn'] . '</td>';
            $html .= '<td>' . number_format($metrics['precision'] * 100, 2) . '%</td>';
            $html .= '<td>' . number_format($metrics['recall'] * 100, 2) . '%</td>';
            $html .= '<td>' . number_format($metrics['f1_score'] * 100, 2) . '%</td>';
            $html .= '</tr>';
        }

        // Akurasi keseluruhan
        $html .= '<tr class="overall">';
        $html .= '<th colspan="7">Akurasi Keseluruhan</th>';
        $html .= '<td>' . number_format($this->getAccuracy() * 100, 2) . '%</td>';
        $html .= '</tr>';

        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }
}
