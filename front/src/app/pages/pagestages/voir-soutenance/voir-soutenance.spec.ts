import { ComponentFixture, TestBed } from '@angular/core/testing';

import { VoirSoutenance } from './voir-soutenance';

describe('VoirSoutenance', () => {
  let component: VoirSoutenance;
  let fixture: ComponentFixture<VoirSoutenance>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [VoirSoutenance]
    })
    .compileComponents();

    fixture = TestBed.createComponent(VoirSoutenance);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
