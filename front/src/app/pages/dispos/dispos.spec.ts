import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Dispos } from './dispos';

describe('Dispos', () => {
  let component: Dispos;
  let fixture: ComponentFixture<Dispos>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Dispos]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Dispos);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
