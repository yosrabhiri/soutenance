import { provideRouter, Routes } from '@angular/router';
import { Home } from './pages/home/home';
import { Dashbord } from './pages/dashbord/dashbord';
import { Stage } from './pages/stage/stage';
import { Annonce } from './pages/annonce/annonce';
import { UserLogin } from './components/user-login/user-login';
import { UserRegister } from './components/user-register/user-register';
import { Ncomp } from './components/navBar/ncomp';
import { AuthGuard } from './AuthGuard';
import { UnauthorizedComponent } from './unauthorized';
import { AnnonceStage } from './pages/pagestages/annonce-stage/annonce-stage';
import { Encadrement } from './pages/pagestages/encadrementPages/encadrement/encadrement';
import { ValidStage } from './pages/pagestages/valid-stage/valid-stage';
import { VoirSoutenance } from './pages/pagestages/voir-soutenance/voir-soutenance';
import { MesSoutenances } from './pages/pagestages/mes-soutenances/mes-soutenances';
import { ToutesSoutenances } from './pages/pagestages/toutes-soutenances/toutes-soutenances';
import { Historique } from './components/etudiant/historique/historique';
import { DemandEncadrement } from './pages/pagestages/encadrementPages/demand-encadrement/demand-encadrement';
import { Rapport } from './pages/rapport/rapport/rapport';
import { Reunion } from './pages/reunion/reunion';
import { DateRapportComponent } from './pages/rapport-date/rapport-date';
import { RapportRecherche } from './pages/rapport-recherche/rapport-recherche';
import { Dispos } from './pages/dispos/dispos';
import { PeriodesSoutenanceComponent } from './pages/periodes-soutenance/periodes-soutenance';

/*export const routes: Routes = [
    {path:'',
    pathMatch:'full',
    redirectTo:'register',
   },
    {
    path:'register',
    component:UserRegister
    },
    {path:'home',
    component:Ncomp,
        children: [
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' },
      { path: 'dashboard', component: Dashbord },
      { path: 'stage', component: Stage }
    ]},
    {path:'dashboard',
    component:Dashbord},

    {path:'stage',
    component:Stage,
    },
    {path:'annonce',
        component:Annonce
    }
   ,
    {path:'login',
    component:UserLogin},


];*/
export const routes: Routes = [
    { path: '', pathMatch: 'full', redirectTo: 'login' },
    { path: 'register', component: UserRegister },
    { path: 'login', component: UserLogin },
    { path: 'homee', component: Home },

   { path: '', pathMatch: 'full', redirectTo: 'login' },
    { path: 'register', component: UserRegister },
    { path: 'login', component: UserLogin },

    {
        path: 'home',
        component: Ncomp,
        children: [
        //{ path: '', redirectTo: 'dashboard', pathMatch: 'full' },
        { path: 'dashboard', component: Dashbord , canActivate: [AuthGuard]},
        { path: 'stage', component: Stage , canActivate: [AuthGuard]},
        { path: 'annonce', component: AnnonceStage, canActivate: [AuthGuard] },
        { path: 's/encadrement', component: Encadrement, canActivate: [AuthGuard] },
        { path: 's/valideStage', component: ValidStage, canActivate: [AuthGuard] },
        { path: 's/soutenance', component: MesSoutenances, canActivate: [AuthGuard] },
        { path: 's/toutes-soutenances', component: ToutesSoutenances, canActivate: [AuthGuard] },
        { path: 's/soutenance-preview', component: VoirSoutenance, canActivate: [AuthGuard] },
        { path: 'homee', component: Home, canActivate: [AuthGuard] },
        { path: 's/demencad', component: DemandEncadrement, canActivate: [AuthGuard] },
        { path: 's/historique', component: Home, canActivate: [AuthGuard] },
        { path: 's/rapport', component: Rapport, canActivate: [AuthGuard] },
        { path: 's/reunions', component: Reunion, canActivate: [AuthGuard] },
        { path: 's/rapprotDate', component: DateRapportComponent, canActivate: [AuthGuard] },
        { path: 's/rapprotRecherche', component: RapportRecherche, canActivate: [AuthGuard] },
        { path: 's/Form', component: Stage, canActivate: [AuthGuard] },
        { path: 's/dispo', component: Dispos, canActivate: [AuthGuard] },
        { path: 's/periodes', component: PeriodesSoutenanceComponent, canActivate: [AuthGuard] },
        
        ]
    },
    { path: 'unauthorized', component: UnauthorizedComponent },
    
];
export const routerConfig = provideRouter(routes);
