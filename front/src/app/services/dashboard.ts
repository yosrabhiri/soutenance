import { computed, Injectable, signal } from '@angular/core';
import { Widget } from '../models/dashboard';
import { Subscribers } from '../widgets/subscribers';
import { Views } from '../widgets/views';
import { Stage } from '../widgets/stage';
import { Analytics } from '../widgets/analytics';
import { Analytics2 } from '../widgets/analytics2';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class DashboardService {
  widgets=signal<Widget[]>([
    {
    id: 1,
    label: 'Etudiants Affectés à un stage',
    content: Subscribers,
    rows:1,
    columns:1,
    backgroundColor:'#A8D5BA',
    color:'white',
  },
   {
    id: 2,
    label: 'Etudiants ayant fait un stage à ISSAT',
    content: Views,
     rows:1,
    columns:1,
    backgroundColor:'#CBAACB',
    color:'white',
  },
  {
    id: 3,
    label: 'Etudiants ayant fait un stage à travers ISSAT',
    content: Stage,
     rows:1,
    columns:1,
    backgroundColor:'#AEDFF7',
    color:'white',
  },
    {id: 4,
    label: 'Etudiants affectés par section',
    content: Analytics,
     rows:2,
    columns:2,}
]);
addedWidgets=signal<Widget[]>([
     {
    id: 1,
    label: 'Etudiants Affectés à un stage',
    content: Subscribers,
    rows:1,
    columns:1,
    backgroundColor:'#A8D5BA',
    color:'white',
  },
   {
    id: 2,
    label: 'Etudiants ayant fait un stage à ISSAT',
    content: Views,
     rows:1,
    columns:1,
    backgroundColor:'#CBAACB',
    color:'white',
  },
  {
    id: 3,
    label: 'Etudiants ayant fait un stage Externe',
    content: Stage,
     rows:1,
    columns:1,
    backgroundColor:'#AEDFF7',
    color:'white',
  },
  {
    id: 4,
    label: 'Etudiants affectés par section',
    content: Analytics,
     rows:2,
    columns:2,

  },
  {
    id: 5,
    label: 'Etudiants affectés par section',
    content: Analytics2,
     rows:2,
    columns:2,

  },
]);
widgetsToAdd=computed(()=>{
  const addedIds=this.addedWidgets().map(w=>w.id);
  return this.widgets().filter((w: { id: number; })=>!addedIds.includes(w.id))

})
addWidget(w:Widget){
  this.addedWidgets.set([...this.addedWidgets(),{...w}])
}
updateWidget(id:number,widget:Partial<Widget>){
  const index=this.addedWidgets().findIndex(w=>w.id===id);
  if(index !==-1){
    const newWidgets=[...this.addedWidgets()];
    newWidgets[index]={...newWidgets[index],...widget};
    this.addedWidgets.set(newWidgets);
  }
}
moveWidgetToRight(id:number){
const index=this.addedWidgets().findIndex(w=>w.id===id);
if(index=== this.addedWidgets().length-1){
  return;
}
const newWidgets=[...this.addedWidgets()];
[newWidgets[index],newWidgets[index+1]]=[{...newWidgets[index+1]},{...newWidgets[index]}];
this.addedWidgets.set(newWidgets);
}
moveWidgetToLeft(id:number){
    const index=this.addedWidgets().findIndex(w=>w.id===id);
  if(index=== 0){
    return;
  }
  const newWidgets=[...this.addedWidgets()];
  [newWidgets[index],newWidgets[index-1]]=[{...newWidgets[index-1]},{...newWidgets[index]}];
  this.addedWidgets.set(newWidgets);
}
removeWidget(id:number){
  this.addedWidgets.set(this.addedWidgets().filter(w=>w.id!==id));

}
  constructor(private http: HttpClient) {}
 private apiUrl = `${environment.apiUrl}/dashboard`; 
  getEtudiantsAffectes() {
    return this.http.get<{ value: number }>(`${this.apiUrl}/etudiants-affectes`);

  }

  getEtudiantsISSAT() {
    return this.http.get<{value:number}>(`${this.apiUrl}/etudiants-issat`);
  }

  getEtudiantsExternes() {
    return this.http.get<{value:number}>(`${this.apiUrl}/etudiants-externes`);
  }

 /* getEtudiantsParSection() {
    return this.http.get<{labels:string[], values:number[]}>(`${this.apiUrl}/etudiants-sections`);
  }

  getEtudiantsParSpecialite() {
    return this.http.get<{labels:string[], values:number[]}>(`${this.apiUrl}/etudiants-specialites`);
  }*/
  getEtudiantsLicence(niveau: string): Observable<any> {
    return this.http.get(`${this.apiUrl}/etudiants-par-specialite-licence?niveau=${niveau}`);
  }

  getEtudiantsSuperieur(niveau: string): Observable<any> {
    return this.http.get(`${this.apiUrl}/etudiants-par-specialite-superieur?niveau=${niveau}`);
  }

}
