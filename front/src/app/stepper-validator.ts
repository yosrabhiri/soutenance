import { AbstractControl, ValidationErrors, ValidatorFn } from '@angular/forms';

export function stageDurationValidator(): ValidatorFn {
  return (control: AbstractControl): ValidationErrors | null => {
    const niveau = control.root.get('niveau')?.value; // récupéré du step1
    const start = control.get('start')?.value;
    const end = control.get('end')?.value;

    if (niveau === 'A3' && start && end) {
      const debut = new Date(start);
      const fin = new Date(end);
      console.log("***********",niveau);

      const diffMois =
        (fin.getFullYear() - debut.getFullYear()) * 12 +
        (fin.getMonth() - debut.getMonth());

      if (diffMois < 4) {
        return { stageDurationInvalid: true };
      }
    }
    return null;
  };
}
