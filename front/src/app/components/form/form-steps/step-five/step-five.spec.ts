import { ComponentFixture, TestBed } from '@angular/core/testing';

import { StepFive } from './step-five';

describe('StepFive', () => {
  let component: StepFive;
  let fixture: ComponentFixture<StepFive>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [StepFive]
    })
    .compileComponents();

    fixture = TestBed.createComponent(StepFive);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
