import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RapportRecherche } from './rapport-recherche';

describe('RapportRecherche', () => {
  let component: RapportRecherche;
  let fixture: ComponentFixture<RapportRecherche>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RapportRecherche]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RapportRecherche);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
