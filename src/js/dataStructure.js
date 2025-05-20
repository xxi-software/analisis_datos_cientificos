// import file json with the dataset

async function getData() {
  const data = fetch("../../uploads/student_habits_performance.json").then(
    (response) => response.json()
  );
  const jsonData = await data;
  return Object.values(jsonData);
}

function promedio(data, value) {
  let sum = 0;
  let count = 0;
  for (const key in data) {
    if (data.hasOwnProperty(key)) {
      // Elimina espacios y convierte a número
      const ageStr = data[key][value];
      if (ageStr) {
        const age = parseFloat(ageStr.trim());
        if (!isNaN(age)) {
          sum += age;
          count++;
        }
      }
    }
  }
  const average = count > 0 ? sum / count : 0;
  return average;
}

function promedioPorGenero(data, generoBuscado, campoPromedio) {
  let sum = 0;
  let count = 0;
  for (const item of data) {
    if (
      item[" gender"] &&
      item[" gender"].trim().toLowerCase() ===
        generoBuscado.trim().toLowerCase()
    ) {
      const valor = parseFloat(item[campoPromedio]?.trim()); // Obtén el valor del campo
      if (!isNaN(valor)) {
        sum += valor; // Suma el valor real
        count++;
      }
    }
  }
  return count > 0 ? sum / count : 0; // Calcula el promedio
}

const studen_habits_json = await getData();
const promedio_edad = promedio(studen_habits_json, " age");
const social_media_hours = promedio(studen_habits_json, " social_media_hours");
console.log(studen_habits_json);

function paintData() {
  const ctx = document.getElementById("canvas");

  new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["Promedio Edad", "Promedio Horas en Redes Sociales"],
      datasets: [
        {
          label: "# of Votes",
          data: [promedio_edad, social_media_hours],
          borderWidth: 1,
        },
      ],
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}

paintData();

function genderSocialMediaHours(data) {
  const genders = ["Male", "Female", "Other"];
  const socialMediaMale = promedioPorGenero(
    data,
    " Male  ",
    " social_media_hours"
  );
  const socialMediaFemale = promedioPorGenero(
    data,
    " Female",
    " social_media_hours"
  );
  const socialMediaOther = promedioPorGenero(
    data,
    " Other ",
    " social_media_hours"
  );
  console.log(socialMediaMale);

  paintData2(genders, { socialMediaMale, socialMediaFemale, socialMediaOther });
}

function paintData2(genders, social_media_hours) {
  const { socialMediaMale, socialMediaFemale, socialMediaOther } =
    social_media_hours;
  new Chart(document.getElementById("canvas2"), {
    type: "bar",
    data: {
      labels: genders,
      datasets: [
        {
          label: "Promedio de horas en redes sociales",
          data: [socialMediaMale, socialMediaFemale, socialMediaOther],
          backgroundColor: ["#4e73df", "#1cc88a", "#36b9cc"],
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: true },
        title: {
          display: true,
          text: "Promedio de horas en redes sociales por género",
        },
      },
    },
  });
}

genderSocialMediaHours(studen_habits_json);

// Promedio de horas de sueño y resultados académicos por género

function paintData3() {
  const sleepData = studen_habits_json.map((d) => ({
    x: promedioPorGenero(studen_habits_json, " exam_score"),
    y: 40.5,
  }));

  new Chart(document.getElementById("canvas3"), {
    type: "scatter",
    data: {
      datasets: [
        {
          label: "Horas de sueño vs. Puntaje de examen",
          data: sleepData,
          backgroundColor: "#4e73df",
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        title: {
          display: true,
          text: "Relación entre horas de sueño y puntaje de examen",
        },
      },
      scales: {
        x: { title: { display: true, text: "Horas de sueño" } },
        y: { title: { display: true, text: "Puntaje de examen" } },
      },
    },
  });
}

paintData3();
