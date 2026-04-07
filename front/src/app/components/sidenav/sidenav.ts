import { Menu } from '@angular/cdk/menu';
import { Component, Input, signal } from '@angular/core';
import {MatListModule} from '@angular/material/list';
import { MatIconModule } from '@angular/material/icon';
import { CommonModule } from '@angular/common';
import { Linkitem } from "../linkitem/linkitem";
import { RouterLink } from '@angular/router';
export type menuItem={
  icon:string;
  label:string;
  route?:string;
  subitem?:menuItem[]
}

@Component({
  selector: 'app-sidenav',
  imports: [MatListModule, MatIconModule, CommonModule, Linkitem],
  templateUrl: './sidenav.html',
  styleUrl: './sidenav.css'
})
export class Sidenav {
  sidenavcollap=signal(false);

  
@Input() set collapsed(val:boolean){
  this.sidenavcollap.set(val);
}

  menuItem=signal<menuItem[]>(
   [{icon:'home',
     label:'Acceuil',
     route:'home'
    },
    {icon:'event',
      label:'Emploi du temps',
     //route:'dashbord'
    },
    {icon:'group',
      label:'Liste des groupes',
     route:'annonce'
    },
    {icon:'edit square',
      label:'Support de cours',
     route:'support'
    },
    {icon:'event',
      label:"Calendrier d'exam",
     route:'exam'
    },
    {icon:'note stack',
      label:'Notes',
     //route:'note'
    },
    
     {icon:'settings',
      label:'stage',
     route:'s',
     subitem:
      [{icon:'edit_note',
     label:'Rapport',
     route:'rapport'
    },{icon:'group',
      label:' Encadrement',
      route:'encadrement'
    },
  {icon:'check',
      label:'Validation Stage',
     route:'valideStage'
    },{icon:'calendar_month',
      label:'Mon emploi soutenance',
     route:'soutenance'
    },{icon:'event_note',
      label:'Prévisualisation soutenances',
      route:'soutenance-preview'
    }
  ,{icon:'summarize',
      label:'Demande Encadrement',
     route:'demencad'
    },
    {icon:'event',
      label:'Reunion',
     route:'reunions'
    },
   {icon:'history',
      label:"Historique",
     route:'historique'
    },
     {icon:'edit_note',
      label:"Formulaire",
     route:'Form'
    },
     {icon:'search',
      label:"Trouver rapport",
     route:'rapprotRecherche'
    },
     {icon:'search',
      label:"Disponibilité",
     route:'dispo'
    },
    {icon:'calendar_month',
      label:"Périodes soutenance",
      route:'periodes'
    },
  {icon:'settings',
      label:"Session",
      route:'rapprotDate'
    }]},
    {icon:'event',
      label:"Calendrier d'examens",
     //route:'l'
    },
    {icon:'settings',
      label:'Réglement interne',
     route:'stage'
    }
    ,{
      icon:'dashboard',
      label:'dashboard',
     route:'dashboard'
    }

     
    ]

  )

}
