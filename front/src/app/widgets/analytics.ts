import { CommonModule } from '@angular/common';
import { Component, ElementRef, ViewChild, viewChild } from '@angular/core';
import Chart, { ChartOptions, ChartType } from 'chart.js/auto';
import { DashboardService } from '../services/dashboard';
@Component({
  selector: 'app-analytics',
  imports: [CommonModule],
  templateUrl: './analytics.html',
  styleUrl: './analytics.css'
})
export class Analytics {
//chart=viewChild.required<ElementRef>('chart');
  @ViewChild('chart', { static: true }) chartRef!: ElementRef;

  /*niveauxData: any = {
    1: {
      labels: ['Licence Science info', 'Licence G. Mecanique', 'Licence Electronique', 'Licence Electromecanique'],
      values: [70.5, 55.8, 40.2, 35.0]
    },
    2: {
      labels: ['Licence Science info', 'Licence G. Mecanique', 'Licence Electronique', 'Licence Electromecanique'],
      values: [78.12, 60.11, 50.9, 44.7]
    },
    3: {
      labels: ['Licence Science info', 'Licence G. Mecanique', 'Licence Electronique', 'Licence Electromecanique'],
      values: [81.57, 63.25, 52.95, 47.29]
    }
  };

  chartInstance: any;

  ngOnInit() {
    this.createChart(this.niveauxData[1]); // Niveau 1 par défaut
  }

  createChart(data: any) {
    this.chartInstance = new Chart(this.chartRef.nativeElement, {
      type: 'bar' as ChartType,
      data: {
        labels: data.labels,
        datasets: [{
          data: data.values,
          backgroundColor: ['#3b82f6', '#a855f7', '#facc15', '#a7f3d0'],
          borderRadius: 10,
          //barThickness: 15
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
         maintainAspectRatio: false, 
        plugins: {
          legend: { display: false }
        },
        scales: {
          x: {
            min: 0,
            max: 100,
            ticks: {
              callback: (value) => value + '%'
            },
            grid: {
          display: false  // <-- supprime les lignes horizontales
                  }
          },
          y: {
        grid: {
          display: false  // <-- supprime les lignes verticales
        }
      }
        }
      } as ChartOptions
    });
  }

  onNiveauChange(event: Event) {
    const niveau = (event.target as HTMLSelectElement).value; // cast ici
    const selectedData = this.niveauxData[niveau];
    this.chartInstance.data.labels = selectedData.labels;
    this.chartInstance.data.datasets[0].data = selectedData.values;
    this.chartInstance.update();
  }*/
chartInstance: any;
  niveaux = ['A1', 'A2', 'A3']; // tableau des niveaux (noms réels)

  constructor(private api: DashboardService) {}

  ngOnInit() {
    this.loadData('A1'); // par défaut, charger A1
  }

  loadData(niveau: string) {
    this.api.getEtudiantsLicence(niveau).subscribe(data => {
      this.createOrUpdateChart(data.labels, data.values);
    });
  }

  createOrUpdateChart(labels: string[], values: number[]) {
    if (this.chartInstance) {
      this.chartInstance.data.labels = labels;
      this.chartInstance.data.datasets[0].data = values;
      this.chartInstance.update();
    } else {
      this.chartInstance = new Chart(this.chartRef.nativeElement, {
        type: 'bar' as ChartType,
        data: {
          labels,
          datasets: [{
            data: values,
            backgroundColor: ['#3b82f6','#a855f7','#facc15','#a7f3d0'],
            borderRadius: 10
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            x: {
              min: 0,
              max: 100,
              ticks: { callback: (v) => v + '%' },
              grid: { display: false }
            },
            y: { grid: { display: false } }
          }
        } as ChartOptions
      });
    }
  }

  onNiveauChange(event: Event) {
    const niveau = (event.target as HTMLSelectElement).value;
    this.loadData(niveau);
  }
}
