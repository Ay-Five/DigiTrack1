document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("search");
  const tableBody = document.getElementById("searchTable");

  if (!searchInput || !tableBody) return;

  const rows = Array.from(tableBody.querySelectorAll("tr")).filter(function (row) {
    return !row.querySelector('td[colspan="6"]');
  });

  const emptyRow = document.createElement("tr");
  emptyRow.id = "noSearchResults";
  emptyRow.style.display = "none";
  emptyRow.innerHTML = '<td colspan="6" class="ec">No matching students found.</td>';
  tableBody.appendChild(emptyRow);

  searchInput.addEventListener("input", function () {
    const keyword = this.value.toLowerCase().trim();
    let visibleCount = 0;

    rows.forEach(function (row) {
      const text = row.innerText.toLowerCase();
      const match = text.includes(keyword);
      row.style.display = match ? "" : "none";
      if (match) visibleCount++;
    });

    emptyRow.style.display = visibleCount === 0 ? "" : "none";
  });
});