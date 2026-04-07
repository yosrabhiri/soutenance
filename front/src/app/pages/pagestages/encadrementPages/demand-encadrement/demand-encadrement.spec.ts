import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DemandEncadrement } from './demand-encadrement';

describe('DemandEncadrement', () => {
  let component: DemandEncadrement;
  let fixture: ComponentFixture<DemandEncadrement>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DemandEncadrement]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DemandEncadrement);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
