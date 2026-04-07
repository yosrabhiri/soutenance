import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Ncomp } from './ncomp';

describe('Ncomp', () => {
  let component: Ncomp;
  let fixture: ComponentFixture<Ncomp>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Ncomp]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Ncomp);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
