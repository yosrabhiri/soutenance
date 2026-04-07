import { Component, Inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef, MatDialogContent } from '@angular/material/dialog'
import {MatDialog, MatDialogModule} from '@angular/material/dialog';
import {MatButtonModule} from '@angular/material/button';
@Component({
  selector: 'app-statut-dialogue',
  imports: [MatDialogContent, MatDialogModule,MatButtonModule],
  templateUrl: './statut-dialogue.html',
  styleUrl: './statut-dialogue.css'
})
export class StatutDialogue {
  constructor(
    public dialogRef: MatDialogRef<StatutDialogue>,
    @Inject(MAT_DIALOG_DATA) public data: any
  ) {}

  onConfirm() {
    this.dialogRef.close('confirm');
  }

  onCancel() {
    this.dialogRef.close();
  }



}
