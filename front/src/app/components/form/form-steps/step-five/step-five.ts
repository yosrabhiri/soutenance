import { MatButtonModule } from '@angular/material/button';
import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { StepperService } from '../../../../stepper-service';
import jsPDF from 'jspdf';
import { StageService } from '../../../../services/stage-api';


@Component({
  selector: 'app-step-five',
  imports: [MatCardModule, CommonModule,MatIconModule,MatButtonModule],
  templateUrl: './step-five.html',
  styleUrl: './step-five.css'
})
export class StepFive implements OnInit{
  constructor(public formData:StepperService,private stageService:StageService){}
  generatePdf() {
  const doc = new jsPDF();

  // Définir marges
  const marginLeft = 10;
  let y = 55; // position verticale de départ (laisser un espace pour l'entête)

  // Titre centré
  doc.setFontSize(16);
  doc.setFont("helvetica", "bold");
  doc.text("ATTESTATION DE STAGE", 105, y, { align: "center" });

  y += 20; // espace après le titre

  // Texte d'introduction
  doc.setFontSize(12);
  doc.setFont("times", "normal");
  doc.text(
    "Le Directeur de stage de l’Institut Supérieur de Sciences Appliquées et Technologiques certifie que l’étudiant(e) :",
    marginLeft,
    y,
    { maxWidth: 170 }
  );

  y += 15;

  // Données de l'étudiant
  const etu = this.formData.firstFormGroup.value;
  doc.text(`Nom et Prénom : ${etu.nom || ''} ${etu.prenom || ''}`, marginLeft, y);
  y += 10;
//  doc.text(`Né(e) le : ${etu.dateNaissance || '..................'}`, marginLeft, y);
 // y += 10;
  doc.text(`CIN n° : ${etu.cin || ''}`, marginLeft, y);
  y += 10;
  if(etu.niveau=='A3'){
    doc.text("Inscrit(e) au : 3éme année", marginLeft, y);
  }else{
  if(etu.niveau=='A2'){
 doc.text("Inscrit(e) au : 2éme année", marginLeft, y);}
 else{
  doc.text("Inscrit(e) au : 1ére année", marginLeft, y);}
 }

  y += 10;
  doc.text(`Diplôme : ${etu.diplome || ''}`, marginLeft, y);
  y += 10;
  doc.text(`Spécialité : ${etu.specialite || ''}`, marginLeft, y);

  y += 10;

  // Société
  const soc = this.formData.secondFormGroup.value;
  doc.text("A eu l’accord pour effectuer son stage de fin d’études obligatoire :", marginLeft, y, { maxWidth: 170 });
  y += 10;
  doc.text(`Société : ${soc.nom || ''}`, marginLeft, y);
  y += 10;
  //doc.text(`Adresse : ${soc.adresse || ''}`, marginLeft, y);
  //y += 10;
  //doc.text(`Domaine : ${soc.domaine || ''}`, marginLeft, y);

  //y += 10;

  // Stage
  const stage = this.formData.thirdFormGroup.value;
  doc.text(`Sujet : ${stage.tache || ''}`, marginLeft, y);
  y += 10;
  //doc.text(`Fonction : ${stage.fonction || ''} - Service : ${stage.service || ''}`, marginLeft, y);
  //y += 10;
  doc.text(`Durée : du ${stage.start || ''} jusqu’au ${stage.end || ''}`, marginLeft, y);

  y += 15;

  // Assurance
  doc.text("L’étudiant(e) est assuré(e) durant toute l’année universitaire.", marginLeft, y);

  y += 20;
  // Pied de page : fait à Sousse à gauche et signature à droite
  const pageWidth = doc.internal.pageSize.getWidth();
  const footerY = y;
  doc.setFontSize(12);
  doc.text(`Fait à Sousse, le ${new Date().toLocaleDateString("fr-FR")}`, pageWidth - marginLeft, footerY, { align: "right" });

  doc.text("Signature", pageWidth - marginLeft, footerY+10, { align: "right" });

  doc.save("attestation_stage.pdf");
}

  etat_validation: string = '';

ngOnInit() {
  this.stageService.loadFormData().subscribe(data => {
    this.etat_validation = data.stage.etat_validation;
    console.log(this.etat_validation) ;
    console.log('llerrekjer',this.formData)
  });
}



}



