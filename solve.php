<?php
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['cages']) || !isset($data['hints'])) {
        throw new Exception('Missing cages or hints');
    }

    $cages = $data['cages'];
    $hints = $data['hints'];

    // Convert cages cells to integer arrays due to strict type checking
    foreach ($cages as &$cage) {
        if (isset($cage['cells']) && is_array($cage['cells'])) {
            foreach ($cage['cells'] as &$cell) {
                if (is_array($cell) && count($cell) === 2) {
                    // Ensure each cell is an array of two integers
                    $cell = [(int)$cell[0], (int)$cell[1]];
                } else {
                    throw new Exception('Invalid cage cell format');
                }
            }
        } else {
            throw new Exception('Invalid cages format');
        }
    }

    // Call the actual solver (you must define this)
    $solver = new KillerSudokuSolver($hints, $cages);

    if (! $solver->solve()) {
        echo json_encode([
            'success' => false,
            'message' => 'No solution could be found.',
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'solution' => $solver->getGrid(),
        ]);
    }
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
    ]);
}

class KillerSudokuSolver
{
    private array $grid;
    private array $cages;

    public function __construct(array $grid, array $cages)
    {
        $this->grid = $grid;
        $this->cages = $cages;
    }

    public function solve(): bool
    {
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($this->grid[$row][$col] === 0) {
                    for ($num = 1; $num <= 9; $num++) {
                        if ($this->isValid($row, $col, $num)) {
                            $this->grid[$row][$col] = $num;
                            if ($this->solve()) return true;
                            $this->grid[$row][$col] = 0;
                        }
                    }
                    return false; // No valid number
                }
            }
        }
        return true; // Solved
    }

    private function isValid(int $row, int $col, int $num): bool
    {
        // Row + column check
        for ($i = 0; $i < 9; $i++) {
            if ($this->grid[$row][$i] === $num || $this->grid[$i][$col] === $num) {
                return false;
            }
        }

        // 3x3 box check
        $startRow = (int)($row / 3) * 3;
        $startCol = (int)($col / 3) * 3;
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                if ($this->grid[$startRow + $i][$startCol + $j] === $num) {
                    return false;
                }
            }
        }

        // Is the number big/small enough to be valid?
        $cageIndex = null;
        foreach ($this->cages as $index => $cage) {
            if ($this->isCellInCage($row, $col, $cage['cells'])) {
                $cageIndex = $index;

                $cageInfo = $this->cageInfo($cage['cells']);

                // Validate possible number against two empty cells in cage
                if (($cageInfo['cellCount'] - $cageInfo['filledCount']) == 2) {
                    $nextAvailableCellDigit = $cage['sum'] - $cageInfo['filledSum'] - $num;
                    if ($nextAvailableCellDigit > 9) {
                        return false; // Cage sum too low for this number
                    } elseif ($nextAvailableCellDigit < 1) {
                        return false; // Cage sum too high for this number
                    }
                }
            }
        }

        // Cage constraint check
        if ($cageIndex !== null) {
            $cage = $this->cages[$cageIndex];
            $sum = 0;
            $used = [];

            foreach ($cage['cells'] as [$r, $c]) {
                $val = $this->grid[$r][$c];
                if ($r === $row && $c === $col) $val = $num;
                if ($val > 0) {
                    if (in_array($val, $used)) return false;
                    $used[] = $val;
                    $sum += $val;
                }
            }

            // If cage is full, total must match exactly
            if (count($used) === count($cage['cells']) && $sum !== $cage['sum']) {
                return false;
            }

            // If not full, sum must not exceed target
            if ($sum > $cage['sum']) {
                return false;
            }
        }

        return true;
    }

    private function isCellInCage(int $row, int $col, array $cells): bool
    {
        foreach ($cells as [$r, $c]) {
            if ($row === $r && $col === $c) return true;
        }
        return false;
    }

    private function cageInfo(array $cells): array
    {
        $cageInfo = [
            'cellCount' => count($cells),
            'filledCount' => 0,
            'filledSum' => 0,
        ];
        foreach ($cells as [$r, $c]) {
            $val = $this->grid[$r][$c];

            if ($val > 0) {
                $cageInfo['filledCount']++;
            }

            $cageInfo['filledSum'] += $val;
        }

        return $cageInfo;
    }

    public function getGrid(): array
    {
        return $this->grid;
    }
}
