import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CalendarMeetings } from './calendar-meetings';

describe('CalendarMeetings', () => {
  let component: CalendarMeetings;
  let fixture: ComponentFixture<CalendarMeetings>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CalendarMeetings]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CalendarMeetings);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
