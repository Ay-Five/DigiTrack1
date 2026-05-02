document.addEventListener("contextmenu", (event) => event.preventDefault());
document.addEventListener("keydown", (event) => {
    if (
        event.ctrlKey && (event.key.toLowerCase() === "u" || event.key.toLowerCase() === "s" || event.key.toLowerCase() === "i" || event.key.toLowerCase() === "j") ||
        event.key === "F12" ||
        (event.ctrlKey && event.shiftKey && (event.key === "I" || event.key === "J" || event.key === "C"))
    ) {
        event.preventDefault();
    }
});

document.querySelectorAll("form").forEach((form) => {
    form.setAttribute("autocomplete", "off");
});
document.querySelectorAll("input").forEach((input) => {
    input.setAttribute("autocomplete", "off");
});
