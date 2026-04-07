import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RapportDate } from './rapport-date';

describe('RapportDate', () => {
  let component: RapportDate;
  let fixture: ComponentFixture<RapportDate>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RapportDate]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RapportDate);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
