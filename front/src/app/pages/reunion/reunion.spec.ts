import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Reunion } from './reunion';

describe('Reunion', () => {
  let component: Reunion;
  let fixture: ComponentFixture<Reunion>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Reunion]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Reunion);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
