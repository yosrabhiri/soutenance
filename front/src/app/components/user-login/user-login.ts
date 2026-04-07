import { Component, inject } from '@angular/core';
import {FormsModule}from '@angular/forms';
import { CommonModule, JsonPipe } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { Router,RouterLink } from '@angular/router';
import { AuthService } from '../../services/auth';
import { tap } from 'rxjs';
import { HttpClient, HttpClientModule } from '@angular/common/http';
@Component({
  selector: 'app-user-login',
  standalone:true,
  imports: [FormsModule,CommonModule,MatCardModule,MatInputModule,MatButtonModule,MatIconModule,MatFormFieldModule,RouterLink,HttpClientModule],
  templateUrl: './user-login.html',
  styleUrl: './user-login.css'
})
export class UserLogin {
  /*user={
    email:"",
    password:""
  };
  storedUser={
    email:"test@gmail.com",
    password:"password123",
  };*/
  email: string = '';
  password: string = '';
  loginValid:boolean=true;
  router=inject(Router);
  private http: HttpClient

  apiUrl: any;
  /*validateLogin(email:string,password:string):boolean{
    return email===this.storedUser.email && password===this.storedUser.password;
  }
  login(){
    if(this.validateLogin(this.user.email,this.user.password)){
      localStorage.setItem('loggedInUser',JSON.stringify(this.user.email));
      this.loginValid=true;
      this.router.navigate(['/home']);

    }else{
      //alert("incorrecte email ou password ")
      this.loginValid=false;
    }
  }*/
constructor(private authService: AuthService) {}

  loginUser() {
  this.authService.login(this.email, this.password).subscribe(
    (res: any) => {
      localStorage.setItem('token', res.authorisation.token); // stocke le token
      console.log('Login réussi', res);
      this.router.navigate(['/home']); // redirection
    },
    err => {
      console.error('Erreur login', err);
      this.loginValid = false;
    }
  );
}

}
