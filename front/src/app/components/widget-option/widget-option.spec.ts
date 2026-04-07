import { ComponentFixture, TestBed } from '@angular/core/testing';

import { WidgetOption } from './widget-option';

describe('WidgetOption', () => {
  let component: WidgetOption;
  let fixture: ComponentFixture<WidgetOption>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [WidgetOption]
    })
    .compileComponents();

    fixture = TestBed.createComponent(WidgetOption);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
