import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Encadrement } from './encadrement';

describe('Encadrement', () => {
  let component: Encadrement;
  let fixture: ComponentFixture<Encadrement>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Encadrement]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Encadrement);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
