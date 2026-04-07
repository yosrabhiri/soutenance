import { ComponentFixture, TestBed } from '@angular/core/testing';

import { OpenCalendarModal } from './open-calendar-modal';

describe('OpenCalendarModal', () => {
  let component: OpenCalendarModal;
  let fixture: ComponentFixture<OpenCalendarModal>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [OpenCalendarModal]
    })
    .compileComponents();

    fixture = TestBed.createComponent(OpenCalendarModal);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
