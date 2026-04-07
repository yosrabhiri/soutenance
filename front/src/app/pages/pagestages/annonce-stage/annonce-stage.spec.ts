import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AnnonceStage } from './annonce-stage';

describe('AnnonceStage', () => {
  let component: AnnonceStage;
  let fixture: ComponentFixture<AnnonceStage>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AnnonceStage]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AnnonceStage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
