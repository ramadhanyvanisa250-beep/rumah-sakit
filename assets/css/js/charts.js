// Gender Distribution Chart
const genderCtx = document.getElementById('genderChart').getContext('2d');
const genderChart = new Chart(genderCtx, {
    type: 'doughnut',
    data: {
        labels: ['Laki-laki', 'Perempuan'],
        datasets: [{
            data: [<?php echo $genderStats['Laki-laki'] ?? 0; ?>, <?php echo $genderStats['Perempuan'] ?? 0; ?>],
            backgroundColor: ['#36A2EB', '#FF6384'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Registration Chart
const regCtx = document.getElementById('registrationChart').getContext('2d');
const regChart = new Chart(regCtx, {
    type: 'bar',
    data: {
        labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
        datasets: [{
            label: 'Jumlah Pendaftaran',
            data: [12, 19, 8, 15],
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 5
                }
            }
        }
    }
});