import { Component, computed, inject, OnInit, signal } from '@angular/core';
import { Records } from '../../records';
import { FormsModule } from '@angular/forms';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import {MatToolbarModule} from '@angular/material/toolbar';
import { MatIcon, MatIconModule } from '@angular/material/icon';

import {MatSidenavModule} from '@angular/material/sidenav';
import { Sidenav } from '../sidenav/sidenav';
import { RouterOutlet } from '@angular/router';
import {Router} from '@angular/router';
import { MatButton, MatButtonModule } from '@angular/material/button';
import { BehaviorSubject } from 'rxjs';
import { AuthService } from '../../services/auth';
import { NotificationService } from '../../services/notification-service';
import { MatMenuModule } from '@angular/material/menu';
import { CommonModule } from '@angular/common';

import {MatBadgeModule} from '@angular/material/badge';


@Component({
  selector: 'app-ncomp',
  imports: [MatBadgeModule,FormsModule, MatSlideToggleModule, MatToolbarModule, MatIcon, MatSidenavModule, Sidenav, RouterOutlet,MatButton,MatMenuModule,
    MatIconModule,
    MatButtonModule, CommonModule],
  providers:[Records],
  templateUrl: './ncomp.html',
  styleUrl: './ncomp.css'
})
export class Ncomp implements OnInit{
  notifications: any[] = [];
  collapsed=signal(false);
  navwidth=computed(()=>this.collapsed()?'65px':'250px');
  infoRecieved: string[]=[];
  http: any;
  unreadCount = 0;
  showNotifications = false;
  onSubmit(){
    console.log('hoho');
  }
  ooo(){
    console.log('lololo')
  }
  getInfos(){
    this.infoRecieved=this.rec.getinfo();
  }
  constructor(private rec:Records,private auth: AuthService,private notifService: NotificationService){
  }
  router=inject(Router);

  isLoggedIn = new BehaviorSubject<boolean>(!!localStorage.getItem('token'));


onLogout() {
  this.auth.logout();
}
ngOnInit(): void {
    this.loadNotifications();

    // Recharger toutes les 100s
    setInterval(() => this.loadNotifications(), 100000);
    
  }

  loadNotifications() {
  this.notifService.getNotifications().subscribe((data: any)  => {
    console.log(data); // pour vérifier la structure
    this.notifications = data.notifications || []; // <-- prend le tableau
    this.unreadCount = this.notifications.filter(n => !n.read_at).length;
  });
}

  toggleNotifications() {
    this.showNotifications = !this.showNotifications;
  }

  showMessage(notif: any) {
  alert(notif.data.message || 'Pas de message');

  if (notif.data.route) {
    this.router.navigate([notif.data.route]);
    console.log(notif.data.route);
  }

  // Marquer comme lu
  if (!notif.read_at) {
    this.notifService.markAsRead(notif.id).subscribe(() => {
      notif.read_at = new Date();
      this.unreadCount = this.notifications.filter(n => !n.read_at).length;
    });
  }
}


  markAsRead(id: string) {
    // 🔹 Ici tu peux appeler ton API Laravel pour marquer la notif comme lue
    this.notifService.markAsRead(id).subscribe(() => this.loadNotifications());
  }



}