import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Linkitem } from './linkitem';

describe('Linkitem', () => {
  let component: Linkitem;
  let fixture: ComponentFixture<Linkitem>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Linkitem]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Linkitem);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
