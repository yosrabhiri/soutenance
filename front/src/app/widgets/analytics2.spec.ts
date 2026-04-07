import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Analytics2 } from './analytics2';

describe('Analytics2', () => {
  let component: Analytics2;
  let fixture: ComponentFixture<Analytics2>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Analytics2]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Analytics2);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
