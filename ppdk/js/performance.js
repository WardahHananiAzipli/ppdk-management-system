function fetchTrainee() {
  const traineeId = document.getElementById('trainee_id').value;
  if (!traineeId) return;

  fetch('get_trainee_performance.php?trainee_id=' + traineeId)
    .then(response => response.json())
    .then(data => {
      // Maklumat asas
      document.getElementById('ic').value = data.ic || '';
      document.getElementById('dob').value = data.dob || '';

      if (data.gender === "Lelaki") {
        document.getElementById('gender_male').checked = true;
      } else if (data.gender === "Perempuan") {
        document.getElementById('gender_female').checked = true;
      }

      const okuValues = (data.oku_categories || "").split(",");
      document.querySelectorAll('input[name="oku_categories[]"]').forEach(cb => {
        cb.checked = okuValues.includes(cb.value);
      });

      const toolsUsed = (data.assistive_tools_used || "").split(",");
      document.querySelectorAll('input[name="assistive_tools_used[]"]').forEach(cb => {
        cb.checked = toolsUsed.includes(cb.value);
      });

      document.querySelector('input[name="assistive_tools_needed"]').value = data.assistive_tools_needed || '';

      // Motor Skills - Tick ikut minggu
      const skills = data.motor_skills || {};
      for (let skill in skills) {
        for (let week in skills[skill]) {
          const val = skills[skill][week];
          const selector = `input[name="motor_skill[${skill}][${week}]"][value="${val}"]`;
          const radio = document.querySelector(selector);
          if (radio) radio.checked = true;
        }
      }
    })
    .catch(err => console.error("Ralat fetch:", err));
}
