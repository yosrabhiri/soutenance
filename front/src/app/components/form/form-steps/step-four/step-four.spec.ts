import { ComponentFixture, TestBed } from '@angular/core/testing';

import { StepFour } from './step-four';

describe('StepFour', () => {
  let component: StepFour;
  let fixture: ComponentFixture<StepFour>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [StepFour]
    })
    .compileComponents();

    fixture = TestBed.createComponent(StepFour);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
