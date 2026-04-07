import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ValidStage } from './valid-stage';

describe('ValidStage', () => {
  let component: ValidStage;
  let fixture: ComponentFixture<ValidStage>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ValidStage]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ValidStage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
