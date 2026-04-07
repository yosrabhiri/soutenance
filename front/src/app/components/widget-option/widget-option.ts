import { Component, inject, input, model } from '@angular/core';
import {  MatButtonModule } from '@angular/material/button';
import { MatButtonToggle, MatButtonToggleModule } from '@angular/material/button-toggle';
import { MatIconModule } from '@angular/material/icon';
import { Widget } from '../../models/dashboard';
import { DashboardService } from '../../services/dashboard';

@Component({
  selector: 'app-widget-option',
  imports: [MatButtonModule,MatIconModule,MatButtonToggleModule],
  templateUrl: './widget-option.html',
  styleUrl: './widget-option.css'
})
export class WidgetOption {
  data=input.required<Widget>();
  showOptions=model<boolean>(false);
store=inject(DashboardService);

}
