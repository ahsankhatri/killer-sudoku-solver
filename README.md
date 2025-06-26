# Killer Sudoku Solver 🎯

A simple web-based Killer Sudoku grid editor and solver.

### ✅ Features
- **9x9 Killer Sudoku Grid UI** where users can:
  - Manually fill cell values
  - Enter cage sums (for Killer Sudoku logic)
  - Use keyboard shortcuts for fast input (yet to document)
- **"Solve" Button** triggers an XHR request to a **PHP backend solver**
- Solves the puzzle and renders the solved grid back in the browser

### 🚀 How It Works
1. User fills the grid manually (cell values + cage sums)
2. Clicks "Solve"
3. Browser sends the current puzzle state via XHR to a PHP script
4. PHP solver returns the solved puzzle as JSON
5. Grid UI updates with the solution

### 🛠️ Technologies Used
- HTML / CSS / JavaScript (Frontend UI)
- PHP (Backend Solver)
- XHR / JSON for frontend-backend communication

### 🎉 Credits
Made with ❤️ and completely from **ChatGPT (by OpenAI)** with a few modifications.

---

Feel free to fork, modify, or extend!

