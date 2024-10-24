import "./bootstrap";

import { marked } from "marked";

// Configuration de marked
marked.setOptions({
    breaks: true, // Permet les retours à la ligne avec un seul \n
    gfm: true, // Active GitHub Flavored Markdown
});

// Rend disponible marked globalement
window.marked = marked;
