import { TestBed } from '@angular/core/testing';

import { AgePromptService } from './age-prompt.service';

describe('AgePromptService', () => {
  let service: AgePromptService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AgePromptService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
