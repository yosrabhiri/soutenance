import { menuItem } from './../sidenav/sidenav';
import { Component, input, signal } from '@angular/core';
import { MatIconModule } from '@angular/material/icon';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { CommonModule } from '@angular/common';
import { MatListModule } from '@angular/material/list';

@Component({
  selector: 'app-linkitem',
  imports: [MatListModule,MatIconModule,CommonModule,RouterLink,RouterLinkActive],
  templateUrl: './linkitem.html',
  styleUrl: './linkitem.css'
})
export class Linkitem {
  item=input.required<menuItem>()
  collapsed=input(false)
  nestedMenu=signal(false)
  toggleNested(){
    if (!this.item().subitem){
return;
    }
    else{
      this.nestedMenu.set(!this.nestedMenu())
    }
  }

}
