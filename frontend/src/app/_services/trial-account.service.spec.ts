import { TestBed } from '@angular/core/testing';

import { TrialAccountService } from './trial-account.service';

describe('TrialAccountService', () => {
  let service: TrialAccountService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(TrialAccountService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
