/* =========================================================
   SLW SUPER ADMIN - DASHBOARD JAVASCRIPT
   Used only on dashboard.html
========================================================= */

document.addEventListener("DOMContentLoaded", function () {
  const chartContainer = document.getElementById("dashboardChart");
  const periodSelect = document.querySelector(".revenue-card .form-select");

  const chartData = {
    sixMonths: [
      { month: "Apr", revenue: 42, topup: 20 },
      { month: "May", revenue: 55, topup: 33 },
      { month: "Jun", revenue: 48, topup: 26 },
      { month: "Jul", revenue: 73, topup: 51 },
      { month: "Aug", revenue: 64, topup: 42 },
      { month: "Sep", revenue: 88, topup: 66 }
    ],

    thisYear: [
      { month: "Jan", revenue: 35, topup: 18 },
      { month: "Mar", revenue: 47, topup: 29 },
      { month: "May", revenue: 58, topup: 36 },
      { month: "Jul", revenue: 72, topup: 48 },
      { month: "Aug", revenue: 66, topup: 44 },
      { month: "Sep", revenue: 91, topup: 69 }
    ]
  };

  /* Create chart bars */
  function renderChart(data) {
    if (!chartContainer) {
      return;
    }

    chartContainer.innerHTML = data
      .map(function (item) {
        return `
          <div class="bar-item">
            <div class="bar-columns">
              <span
                class="revenue-bar"
                style="height: ${item.revenue}%"
                title="Revenue: ${item.revenue}%"
              ></span>

              <span
                class="topup-bar"
                style="height: ${item.topup}%"
                title="Agent top-ups: ${item.topup}%"
              ></span>
            </div>

            <span>${item.month}</span>
          </div>
        `;
      })
      .join("");
  }

  /* Display default six-month chart */
  renderChart(chartData.sixMonths);

  /* Change chart when reporting period changes */
  if (periodSelect) {
    periodSelect.addEventListener("change", function () {
      const selectedPeriod = periodSelect.value;

      if (selectedPeriod === "This year") {
        renderChart(chartData.thisYear);
      } else {
        renderChart(chartData.sixMonths);
      }
    });
  }

  /* Notification demo */
  const notificationButton = document.querySelector(".notification-button");

  if (notificationButton) {
    notificationButton.addEventListener("click", function () {
      const notificationDot = notificationButton.querySelector(
        ".notification-dot"
      );

      if (notificationDot) {
        notificationDot.style.display = "none";
      }
    });
  }
});