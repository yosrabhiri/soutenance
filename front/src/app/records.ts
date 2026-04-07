import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class Records {
  infos1: string[]=['yoo','bello'];
  infos2: string[]=['yoo2','bello2'];
  getinfo(): string[]{
    return(this.infos1)
  };
  constructor(){}

  
}
