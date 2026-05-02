//pang arte lg ni haha
document.addEventListener("DOMContentLoaded", () => {
  const toasts = document.querySelectorAll(".toast");

  toasts.forEach((toast, index) => {
    // delay each toast slightly if multiple
    setTimeout(() => {
      toast.classList.add("show");
    }, 100 * index);

    // auto remove
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => toast.remove(), 300);
    }, 3000); // 3 seconds
  });
});
