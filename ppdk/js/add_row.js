function addProgramRow() {
  const tbody = document.getElementById('program-table-body');
  const rowCount = tbody.rows.length + 1;

  const row = document.createElement('tr');
  row.innerHTML = `
    <td>${rowCount}</td>
    <td><input type="text" name="program_name[]" class="form-control"></td>
    <td><input type="text" name="position_held[]" class="form-control"></td>
    <td><input type="text" name="year[]" class="form-control"></td>
    <td><button type="button" class="btn-remove-row">✖</button></td>
  `;
  tbody.appendChild(row);
  renumberRows();
}

document.addEventListener('click', function(e) {
  if (e.target.classList.contains('btn-remove-row')) {
    const row = e.target.closest('tr');
    row.remove();
    renumberRows();
  }
});

function renumberRows() {
  const rows = document.querySelectorAll('#program-table-body tr');
  rows.forEach((row, index) => {
    row.querySelector('td').textContent = index + 1;
  });
}

