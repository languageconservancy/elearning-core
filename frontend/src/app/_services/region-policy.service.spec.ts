import { TestBed } from '@angular/core/testing';

import { RegionPolicyService } from './region-policy.service';

describe('RegionPolicyService', () => {
  let service: RegionPolicyService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(RegionPolicyService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
