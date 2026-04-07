import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ReunionDialogue } from './reunion-dialogue';

describe('ReunionDialogue', () => {
  let component: ReunionDialogue;
  let fixture: ComponentFixture<ReunionDialogue>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ReunionDialogue]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ReunionDialogue);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
