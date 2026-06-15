import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})

export class NotificationService {
  private apiUrl = environment.apiUrl;

  markAsRead(id: string) {
  return this.http.post(`${this.apiUrl}/notifications/read/${id}`, {});
}

  constructor(private http: HttpClient) {}

  getNotifications() {
    return this.http.get<any[]>(`${this.apiUrl}/notification`);
  }
}
