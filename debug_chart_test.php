<?php
// Debug chart visibility
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chart Debug Test</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { margin: 20px; font-family: Arial; }
        .debug-info { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .chart-wrapper { 
            width: 100%; 
            height: 400px; 
            background: white; 
            border: 2px solid #333; 
            position: relative;
            margin: 20px 0;
        }
        #debugChart { 
            width: 100% !important; 
            height: 380px !important; 
            display: block !important;
        }
    </style>
</head>
<body>
    <h1>Chart Debug Test</h1>
    
    <div class="debug-info" id="debugInfo">
        <h3>Debug Information:</h3>
        <p id="chartJsStatus">Checking Chart.js...</p>
        <p id="domStatus">Checking DOM...</p>
        <p id="canvasStatus">Checking Canvas...</p>
        <p id="chartStatus">Checking Chart Creation...</p>
    </div>
    
    <h2>Chart Test Area:</h2>
    <div class="chart-wrapper">
        <canvas id="debugChart"></canvas>
    </div>
    
    <div class="debug-info">
        <button onclick="recreateChart()">Recreate Chart</button>
        <button onclick="logChartInfo()">Log Chart Info</button>
        <button onclick="forceUpdate()">Force Update</button>
    </div>

    <script>
        let debugChart = null;
        
        function updateStatus(elementId, message, isSuccess = true) {
            const element = document.getElementById(elementId);
            element.innerHTML = message;
            element.style.color = isSuccess ? 'green' : 'red';
        }
        
        function initDebugChart() {
            console.log('🔧 Starting debug chart initialization...');
            
            // 1. Check Chart.js
            if (typeof Chart === 'undefined') {
                updateStatus('chartJsStatus', '❌ Chart.js not loaded!', false);
                return;
            }
            updateStatus('chartJsStatus', '✅ Chart.js loaded successfully');
            
            // 2. Check DOM
            if (document.readyState !== 'complete') {
                updateStatus('domStatus', '⏳ DOM still loading...', false);
                return;
            }
            updateStatus('domStatus', '✅ DOM loaded completely');
            
            // 3. Check Canvas
            const canvas = document.getElementById('debugChart');
            if (!canvas) {
                updateStatus('canvasStatus', '❌ Canvas element not found!', false);
                return;
            }
            updateStatus('canvasStatus', '✅ Canvas element found: ' + canvas.tagName);
            
            // 4. Create Chart
            try {
                const ctx = canvas.getContext('2d');
                
                debugChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00'],
                        datasets: [{
                            label: 'Temperature (°C)',
                            data: [25.2, 25.8, 26.1, 26.5, 27.0, 26.8, 26.3, 25.9],
                            borderColor: '#FF6384',
                            backgroundColor: 'rgba(255, 99, 132, 0.1)',
                            fill: true,
                            tension: 0.4
                        }, {
                            label: 'pH Level',
                            data: [6.8, 6.9, 7.0, 4.0, 4.1, 4.2, 4.0, 3.9],
                            borderColor: '#36A2EB',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            fill: true,
                            tension: 0.4
                        }, {
                            label: 'Oxygen (mg/L)',
                            data: [7.5, 7.2, 6.9, 6.8, 6.5, 6.7, 7.0, 7.1],
                            borderColor: '#4BC0C0',
                            backgroundColor: 'rgba(75, 192, 192, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Debug Chart - Working Hours Data',
                                font: { size: 16 }
                            },
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Values'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Time (Working Hours)'
                                }
                            }
                        }
                    }
                });
                
                updateStatus('chartStatus', '✅ Chart created successfully!');
                console.log('✅ Debug chart created:', debugChart);
                
            } catch (error) {
                updateStatus('chartStatus', '❌ Chart creation failed: ' + error.message, false);
                console.error('❌ Chart creation error:', error);
            }
        }
        
        function recreateChart() {
            if (debugChart) {
                debugChart.destroy();
            }
            initDebugChart();
        }
        
        function logChartInfo() {
            if (debugChart) {
                console.log('📊 Chart Info:', {
                    chart: debugChart,
                    data: debugChart.data,
                    canvas: debugChart.canvas,
                    visible: debugChart.canvas.offsetWidth > 0 && debugChart.canvas.offsetHeight > 0
                });
            } else {
                console.log('❌ No chart available');
            }
        }
        
        function forceUpdate() {
            if (debugChart) {
                debugChart.update('active');
                console.log('🔄 Chart force updated');
            }
        }
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDebugChart);
        } else {
            setTimeout(initDebugChart, 100);
        }
    </script>
</body>
</html>