import { Component, ElementRef, inject, viewChild } from '@angular/core';
import { WidgetComponent } from '../../components/widget/widget';
import { DashboardService } from '../../services/dashboard';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { CommonModule } from '@angular/common';
import{wrapGrid} from 'animate-css-grid';

@Component({
  selector: 'app-dashbord',
  standalone:true,
  imports: [WidgetComponent,MatButtonModule,MatIconModule,MatMenuModule,CommonModule],
  providers:[DashboardService],
  templateUrl: './dashbord.html',
  styleUrl: './dashbord.css'
})
export class Dashbord {
store=inject(DashboardService);
dashboard = viewChild.required<ElementRef>('dashboard');
ngOnInit(){
  wrapGrid(this.dashboard().nativeElement,{duration:300});
}
}
