import { Component, input, signal } from '@angular/core';
import { Widget } from '../../models/dashboard';
import { NgComponentOutlet } from '@angular/common';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { WidgetOption } from '../widget-option/widget-option';
@Component({
  selector: 'app-widget',
  standalone:true,
  imports: [NgComponentOutlet,MatButtonModule,MatIconModule,WidgetOption],
  templateUrl: './widget.html',
  styleUrl: './widget.css'
})
export class WidgetComponent {
data=input.required<Widget>();
 showOptions=signal(false);
}


