import { ComponentFixture, TestBed } from '@angular/core/testing';

import { StatutDialogue } from './statut-dialogue';

describe('StatutDialogue', () => {
  let component: StatutDialogue;
  let fixture: ComponentFixture<StatutDialogue>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [StatutDialogue]
    })
    .compileComponents();

    fixture = TestBed.createComponent(StatutDialogue);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
